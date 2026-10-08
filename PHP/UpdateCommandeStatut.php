<?php

session_start();

require __DIR__ . "/db.php";
require __DIR__ . "/MongoDb.php";
require_once __DIR__ . "/Mailer.php";

header("Content-Type: application/json; charset=utf-8");


/**
 * Vérification de la connexion.
 */
if (!isset($_SESSION["utilisateur_id"])) {

    http_response_code(401);

    echo json_encode([
        "success" => false,
        "message" => "Utilisateur non connecté."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/**
 * Vérification du rôle :
 * Employé OU Administrateur.
 */
$sqlRole = "
    SELECT role.libelle
    FROM possede_utilisateur_role

    INNER JOIN role
        ON possede_utilisateur_role.role_id = role.role_id

    WHERE possede_utilisateur_role.utilisateur_id = :utilisateur_id
    AND role.libelle IN ('Administrateur', 'Employé')

    LIMIT 1
";

$stmtRole = $pdo->prepare($sqlRole);

$stmtRole->execute([
    "utilisateur_id" => $_SESSION["utilisateur_id"]
]);

$role = $stmtRole->fetch(PDO::FETCH_ASSOC);

if (!$role) {

    http_response_code(403);

    echo json_encode([
        "success" => false,
        "message" => "Accès refusé."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/**
 * POST uniquement.
 */
if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    http_response_code(405);

    echo json_encode([
        "success" => false,
        "message" => "Méthode non autorisée."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/**
 * Vérification CSRF.
 */
$csrfToken =
    $_SERVER["HTTP_X_CSRF_TOKEN"] ?? "";

if (
    !isset($_SESSION["csrf_token"]) ||
    !is_string($_SESSION["csrf_token"]) ||
    !is_string($csrfToken) ||
    !hash_equals(
        $_SESSION["csrf_token"],
        $csrfToken
    )
) {

    http_response_code(403);

    echo json_encode([
        "success" => false,
        "message" => "Jeton de sécurité invalide."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/**
 * Lecture du JSON.
 */
$donnees = json_decode(
    file_get_contents("php://input"),
    true
);

if (!is_array($donnees)) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Données JSON invalides."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


$numeroCommande = trim(
    (string) ($donnees["numero_commande"] ?? "")
);

$nouveauStatut = trim(
    (string) ($donnees["statut"] ?? "")
);

$modeContact = trim(
    (string) ($donnees["mode_contact"] ?? "")
);

$motif = trim(
    (string) ($donnees["motif"] ?? "")
);


if (
    $numeroCommande === "" ||
    $nouveauStatut === ""
) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Données manquantes."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/**
 * Statuts acceptés par le serveur.
 */
$statutsAutorises = [
    "acceptée",
    "en préparation",
    "en cours de livraison",
    "livré",
    "en attente du retour de matériel",
    "terminée",
    "refusée"
];

if (
    !in_array(
        $nouveauStatut,
        $statutsAutorises,
        true
    )
) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Statut invalide."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/**
 * Un refus nécessite obligatoirement
 * un contact préalable avec le client.
 */
if ($nouveauStatut === "refusée") {

    $modeContact = strtolower($modeContact);

    if (
        !in_array(
            $modeContact,
            ["mail", "gsm"],
            true
        )
    ) {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" =>
                "Le mode de contact est obligatoire."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    if ($motif === "") {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" =>
                "Le motif du refus est obligatoire."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }
}


/**
 * Ces informations ne sont utilisées
 * que lorsqu'elles sont nécessaires.
 */
if ($modeContact === "") {
    $modeContact = null;
}

if ($motif === "") {
    $motif = null;
}


try {

    $pdo->beginTransaction();


    /**
     * Récupération et verrouillage
     * de la commande.
     */
    $sqlCommande = "
        SELECT
            numero_commande,
            statut,
            nombre_personne,
            pret_materiel,
            restitution_materiel

        FROM commande

        WHERE numero_commande = :numero_commande

        LIMIT 1

        FOR UPDATE
    ";

    $stmtCommande =
        $pdo->prepare($sqlCommande);

    $stmtCommande->execute([
        "numero_commande" => $numeroCommande
    ]);

    $commande =
        $stmtCommande->fetch(PDO::FETCH_ASSOC);


    if (!$commande) {

        $pdo->rollBack();

        http_response_code(404);

        echo json_encode([
            "success" => false,
            "message" => "Commande introuvable."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    $ancienStatut =
        strtolower(
            trim((string) $commande["statut"])
        );

    $nombrePersonnes =
        (int) $commande["nombre_personne"];

    $pretMateriel =
        (int) $commande["pret_materiel"] === 1;


    /**
     * Les commandes déjà terminées,
     * refusées ou annulées ne peuvent
     * plus changer de statut.
     */
    if (
        in_array(
            $ancienStatut,
            [
                "terminée",
                "refusée",
                "annulée"
            ],
            true
        )
    ) {

        $pdo->rollBack();

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" =>
                "Cette commande ne peut plus être modifiée."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    /**
     * Workflow autorisé.
     */
    $transitionAutorisee = false;


    if (
        $ancienStatut === "en attente" &&
        in_array(
            $nouveauStatut,
            ["acceptée", "refusée"],
            true
        )
    ) {

        $transitionAutorisee = true;
    }


    elseif (
        in_array(
            $ancienStatut,
            ["acceptée", "accepté"],
            true
        ) &&
        $nouveauStatut === "en préparation"
    ) {

        $transitionAutorisee = true;
    }


    elseif (
        $ancienStatut === "en préparation" &&
        $nouveauStatut ===
            "en cours de livraison"
    ) {

        $transitionAutorisee = true;
    }


    elseif (
        $ancienStatut ===
            "en cours de livraison" &&
        $nouveauStatut === "livré"
    ) {

        $transitionAutorisee = true;
    }


    elseif (
        $ancienStatut === "livré" &&
        !$pretMateriel &&
        $nouveauStatut === "terminée"
    ) {

        $transitionAutorisee = true;
    }


    elseif (
        $ancienStatut === "livré" &&
        $pretMateriel &&
        $nouveauStatut ===
            "en attente du retour de matériel"
    ) {

        $transitionAutorisee = true;
    }


    elseif (
        $ancienStatut ===
            "en attente du retour de matériel" &&
        $pretMateriel &&
        $nouveauStatut === "terminée"
    ) {

        $transitionAutorisee = true;
    }


    if (!$transitionAutorisee) {

        $pdo->rollBack();

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" =>
                "Changement de statut non autorisé."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    /**
     * Restitution du stock lorsqu'une
     * commande en attente est refusée.
     */
    if (
        $nouveauStatut === "refusée" &&
        $ancienStatut === "en attente"
    ) {

        $sqlRestitution = "
            UPDATE menu

            INNER JOIN commande_menu
                ON menu.menu_id =
                   commande_menu.menu_id

            SET menu.quantite_restante =
                menu.quantite_restante
                + :nombre_personnes

            WHERE
                commande_menu.numero_commande =
                :numero_commande
        ";

        $stmtRestitution =
            $pdo->prepare($sqlRestitution);

        $stmtRestitution->execute([
            "nombre_personnes" =>
                $nombrePersonnes,

            "numero_commande" =>
                $numeroCommande
        ]);

        if (
            $stmtRestitution->rowCount() !== 1
        ) {

            throw new RuntimeException(
                "Impossible de restituer le stock."
            );
        }
    }


    /**
     * Si le matériel vient d'être rendu,
     * on l'enregistre.
     */
    if (
        $ancienStatut ===
            "en attente du retour de matériel" &&
        $nouveauStatut === "terminée"
    ) {

        $sqlMateriel = "
            UPDATE commande

            SET restitution_materiel = 1

            WHERE numero_commande =
                :numero_commande
        ";

        $stmtMateriel =
            $pdo->prepare($sqlMateriel);

        $stmtMateriel->execute([
            "numero_commande" =>
                $numeroCommande
        ]);
    }


    /**
     * Modification du statut courant.
     */
    $sqlUpdate = "
        UPDATE commande

        SET statut = :statut

        WHERE numero_commande =
            :numero_commande
    ";

    $stmtUpdate =
        $pdo->prepare($sqlUpdate);

    $stmtUpdate->execute([
        "statut" => $nouveauStatut,

        "numero_commande" =>
            $numeroCommande
    ]);


    /**
     * Historique du changement.
     *
     * CURRENT_TIMESTAMP conserve
     * automatiquement la date et l'heure.
     */
    $sqlHistorique = "
        INSERT INTO historique_commande (
            numero_commande,
            statut,
            date_modification,
            mode_contact,
            motif
        )
        VALUES (
            :numero_commande,
            :statut,
            CURRENT_TIMESTAMP,
            :mode_contact,
            :motif
        )
    ";

    $stmtHistorique =
        $pdo->prepare($sqlHistorique);

    $stmtHistorique->execute([
        "numero_commande" =>
            $numeroCommande,

        "statut" =>
            $nouveauStatut,

        "mode_contact" =>
            $modeContact,

        "motif" =>
            $motif
    ]);


    /**
     * Tout ce qui concerne MySQL
     * est maintenant validé ensemble.
     */
    $pdo->commit();


    /**
     * Récupération des informations
     * client/menu nécessaires après
     * la transaction.
     *
     * Elles servent à MongoDB et
     * aux éventuels e-mails.
     */
    $sqlInformations = "
        SELECT
            c.numero_commande,
            c.date_commande,
            c.date_prestation,
            c.heure_livraison,
            c.prix_menu,
            c.nombre_personne,
            c.prix_livraison,
            c.statut,
            c.pret_materiel,
            c.restitution_materiel,

            u.utilisateur_id,
            u.prenom,
            u.email,
            u.telephone,
            u.ville,

            m.menu_id,
            m.titre AS menu_titre,
            m.regime

        FROM commande c

        INNER JOIN commande_utilisateur cu
            ON c.numero_commande =
               cu.numero_commande

        INNER JOIN utilisateur u
            ON cu.utilisateur_id =
               u.utilisateur_id

        INNER JOIN commande_menu cm
            ON c.numero_commande =
               cm.numero_commande

        INNER JOIN menu m
            ON cm.menu_id =
               m.menu_id

        WHERE c.numero_commande =
            :numero_commande

        LIMIT 1
    ";

    $stmtInformations =
        $pdo->prepare($sqlInformations);

    $stmtInformations->execute([
        "numero_commande" =>
            $numeroCommande
    ]);

    $informations =
        $stmtInformations->fetch(
            PDO::FETCH_ASSOC
        );


    /**
     * E-mail lors de l'attente
     * du retour de matériel.
     */
    if (
        $informations &&
        $nouveauStatut ===
            "en attente du retour de matériel"
    ) {

        try {

            $prenomSecurise =
                htmlspecialchars(
                    $informations["prenom"],
                    ENT_QUOTES,
                    "UTF-8"
                );

            $numeroSecurise =
                htmlspecialchars(
                    $numeroCommande,
                    ENT_QUOTES,
                    "UTF-8"
                );


            $contenuHtml = "
                <h1>Retour du matériel</h1>

                <p>
                    Bonjour {$prenomSecurise},
                </p>

                <p>
                    La commande
                    <strong>{$numeroSecurise}</strong>
                    est maintenant en attente du
                    retour du matériel prêté.
                </p>

                <p>
                    Le matériel doit être restitué
                    dans un délai de 10 jours ouvrés.
                </p>

                <p>
                    Passé ce délai, des frais de
                    600 € pourront être appliqués,
                    conformément aux conditions
                    générales de vente.
                </p>

                <p>
                    Pour restituer le matériel,
                    veuillez prendre contact avec
                    Vite & Gourmand.
                </p>
            ";


            $contenuTexte =
                "Bonjour "
                . $informations["prenom"]
                . ",\n\n"
                . "La commande "
                . $numeroCommande
                . " est maintenant en attente "
                . "du retour du matériel prêté.\n\n"
                . "Le matériel doit être restitué "
                . "dans un délai de 10 jours ouvrés.\n"
                . "Passé ce délai, des frais de "
                . "600 € pourront être appliqués "
                . "conformément aux conditions "
                . "générales de vente.\n\n"
                . "Pour restituer le matériel, "
                . "veuillez prendre contact avec "
                . "Vite & Gourmand.";


            envoyerEmail(
                $informations["email"],
                "Retour du matériel - "
                    . $numeroCommande,
                $contenuHtml,
                $contenuTexte
            );

        } catch (Throwable $mailErreur) {

            /**
             * Une erreur SMTP ne doit pas
             * annuler le changement de statut.
             */
            error_log(
                "Erreur mail matériel : "
                . $mailErreur->getMessage()
            );
        }
    }


    /**
     * E-mail lorsqu'une commande
     * devient terminée.
     */
    if (
        $informations &&
        $nouveauStatut === "terminée"
    ) {

        try {

            $prenomSecurise =
                htmlspecialchars(
                    $informations["prenom"],
                    ENT_QUOTES,
                    "UTF-8"
                );

            $numeroSecurise =
                htmlspecialchars(
                    $numeroCommande,
                    ENT_QUOTES,
                    "UTF-8"
                );


            $contenuHtml = "
                <h1>Commande terminée</h1>

                <p>
                    Bonjour {$prenomSecurise},
                </p>

                <p>
                    Votre commande
                    <strong>{$numeroSecurise}</strong>
                    est maintenant terminée.
                </p>

                <p>
                    Vous pouvez vous connecter
                    à votre compte Vite & Gourmand
                    afin de donner votre avis.
                </p>
            ";


            $contenuTexte =
                "Bonjour "
                . $informations["prenom"]
                . ",\n\n"
                . "Votre commande "
                . $numeroCommande
                . " est maintenant terminée.\n\n"
                . "Vous pouvez vous connecter "
                . "à votre compte Vite & Gourmand "
                . "afin de donner votre avis.";


            envoyerEmail(
                $informations["email"],
                "Commande terminée - "
                    . $numeroCommande,
                $contenuHtml,
                $contenuTexte
            );

        } catch (Throwable $mailErreur) {

            error_log(
                "Erreur mail commande terminée : "
                . $mailErreur->getMessage()
            );
        }
    }


    /**
     * Synchronisation MongoDB.
     *
     * MongoDB reste utilisé pour
     * les statistiques.
     */
    if (
        $mongo !== null &&
        $informations !== false
    ) {

        try {

            $document = [

                "orderNumber" =>
                    $informations[
                        "numero_commande"
                    ],

                "orderedAt" =>
                    $informations[
                        "date_commande"
                    ],

                "prestationDate" =>
                    $informations[
                        "date_prestation"
                    ],

                "status" =>
                    $informations[
                        "statut"
                    ],


                "customer" => [

                    "userId" =>
                        (int) $informations[
                            "utilisateur_id"
                        ],

                    "firstName" =>
                        $informations[
                            "prenom"
                        ],

                    "email" =>
                        $informations[
                            "email"
                        ],

                    "phone" =>
                        $informations[
                            "telephone"
                        ]
                ],


                "menu" => [

                    "menuId" =>
                        (int) $informations[
                            "menu_id"
                        ],

                    "title" =>
                        $informations[
                            "menu_titre"
                        ],

                    "regime" =>
                        $informations[
                            "regime"
                        ]
                ],


                "order" => [

                    "nbPersons" =>
                        (int) $informations[
                            "nombre_personne"
                        ],

                    "menuPrice" =>
                        (float) $informations[
                            "prix_menu"
                        ],

                    "deliveryPrice" =>
                        (float) $informations[
                            "prix_livraison"
                        ],

                    "total" =>
                        (float) $informations[
                            "prix_menu"
                        ]
                        +
                        (float) $informations[
                            "prix_livraison"
                        ]
                ],


                "delivery" => [

                    "city" =>
                        $informations[
                            "ville"
                        ],

                    "time" =>
                        $informations[
                            "heure_livraison"
                        ]
                ]
            ];


            $bulk =
                new MongoDB\Driver\BulkWrite();

            $bulk->update(
                [
                    "orderNumber" =>
                        $numeroCommande
                ],
                [
                    '$set' => $document
                ],
                [
                    "upsert" => true
                ]
            );


            $mongo->executeBulkWrite(
                $mongoDatabase
                    . ".orders_analytics",
                $bulk
            );

        } catch (Throwable $mongoErreur) {

            /**
             * Une erreur MongoDB ne doit
             * pas annuler MySQL.
             */
            error_log(
                "Erreur synchronisation MongoDB : "
                . $mongoErreur->getMessage()
            );
        }
    }


    echo json_encode([
        "success" => true,
        "message" =>
            "Statut de la commande modifié."
    ], JSON_UNESCAPED_UNICODE);


} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log(
        "Erreur UpdateCommandeStatut : "
        . $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" =>
            "Erreur lors de la modification de la commande."
    ], JSON_UNESCAPED_UNICODE);
}