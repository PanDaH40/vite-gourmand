<?php

session_start();

require __DIR__ . "/db.php";
require __DIR__ . "/MongoDb.php";

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
 * Vérification du rôle administrateur.
 */
$sqlRole = "
    SELECT role.libelle
    FROM possede_utilisateur_role
    INNER JOIN role
        ON possede_utilisateur_role.role_id = role.role_id
    WHERE possede_utilisateur_role.utilisateur_id = :utilisateur_id
    AND role.libelle = 'Administrateur'
    LIMIT 1
";

$stmtRole = $pdo->prepare($sqlRole);

$stmtRole->execute([
    "utilisateur_id" => $_SESSION["utilisateur_id"]
]);

$role = $stmtRole->fetch(PDO::FETCH_ASSOC);

if (
    !$role ||
    $role["libelle"] !== "Administrateur"
) {

    http_response_code(403);

    echo json_encode([
        "success" => false,
        "message" => "Accès refusé."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/**
 * Seules les requêtes POST sont autorisées.
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
 * Vérification du jeton CSRF.
 */
$csrfToken = $_SERVER["HTTP_X_CSRF_TOKEN"] ?? "";

if (
    !isset($_SESSION["csrf_token"]) ||
    !is_string($_SESSION["csrf_token"]) ||
    !is_string($csrfToken) ||
    !hash_equals($_SESSION["csrf_token"], $csrfToken)
) {

    http_response_code(403);

    echo json_encode([
        "success" => false,
        "message" => "Jeton de sécurité invalide."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/**
 * Récupération du JSON envoyé par JavaScript.
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
    $donnees["numero_commande"] ?? ""
);

$nouveauStatut = trim(
    $donnees["statut"] ?? ""
);


/**
 * Vérification des données.
 */
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
 * Liste des statuts autorisés.
 */
$statutsAutorises = [
    "en attente",
    "acceptée",
    "refusée",
    "terminée"
];

if (!in_array(
    $nouveauStatut,
    $statutsAutorises,
    true
)) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Statut invalide."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


try {

    /**
     * Début de la transaction MySQL.
     *
     * Le statut et le stock doivent
     * être modifiés ensemble.
     */
    $pdo->beginTransaction();


    /**
     * Récupération et verrouillage
     * de la commande.
     */
    $sqlCommande = "
        SELECT
            numero_commande,
            statut,
            nombre_personne
        FROM commande
        WHERE numero_commande = :numero_commande
        LIMIT 1
        FOR UPDATE
    ";

    $stmtCommande = $pdo->prepare($sqlCommande);

    $stmtCommande->execute([
        "numero_commande" => $numeroCommande
    ]);

    $commande = $stmtCommande->fetch(PDO::FETCH_ASSOC);


    if (!$commande) {

        $pdo->rollBack();

        http_response_code(404);

        echo json_encode([
            "success" => false,
            "message" => "Commande introuvable."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    $ancienStatut = $commande["statut"];

    $nombrePersonnes =
        (int) $commande["nombre_personne"];


    /**
     * Une commande refusée ou terminée
     * ne peut plus être modifiée.
     */
    if (
        in_array(
            $ancienStatut,
            ["refusée", "terminée"],
            true
        )
    ) {

        $pdo->rollBack();

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "Cette commande ne peut plus être modifiée."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    /**
     * Vérification des transitions autorisées.
     *
     * En attente -> Acceptée ou Refusée
     * Acceptée -> Terminée ou Refusée
     */
    $transitionAutorisee =
        (
            $ancienStatut === "en attente" &&
            in_array(
                $nouveauStatut,
                ["acceptée", "refusée"],
                true
            )
        )
        ||
        (
            $ancienStatut === "acceptée" &&
            in_array(
                $nouveauStatut,
                ["terminée", "refusée"],
                true
            )
        );


    if (!$transitionAutorisee) {

        $pdo->rollBack();

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "Changement de statut non autorisé."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    /**
     * Restitution du stock.
     *
     * Si une commande devient refusée,
     * on rend au menu le nombre de places
     * précédemment réservées.
     */
    if ($nouveauStatut === "refusée") {

        $sqlRestitution = "
            UPDATE menu
            INNER JOIN commande_menu
                ON menu.menu_id = commande_menu.menu_id
            SET menu.quantite_restante =
                menu.quantite_restante + :nombre_personnes
            WHERE commande_menu.numero_commande = :numero_commande
        ";

        $stmtRestitution = $pdo->prepare(
            $sqlRestitution
        );

        $stmtRestitution->execute([
            "nombre_personnes" => $nombrePersonnes,
            "numero_commande" => $numeroCommande
        ]);

        if ($stmtRestitution->rowCount() !== 1) {

            throw new RuntimeException(
                "Impossible de restituer le stock de la commande."
            );
        }
    }


    /**
     * Modification du statut dans MySQL.
     */
    $sqlUpdate = "
        UPDATE commande
        SET statut = :statut
        WHERE numero_commande = :numero_commande
    ";

    $stmtUpdate = $pdo->prepare($sqlUpdate);

    $stmtUpdate->execute([
        "statut" => $nouveauStatut,
        "numero_commande" => $numeroCommande
    ]);


    /**
     * Validation de la transaction.
     *
     * Le statut et le stock sont
     * enregistrés définitivement.
     */
    $pdo->commit();


    /**
     * Synchronisation MongoDB.
     *
     * On synchronise les commandes acceptées
     * ou terminées pour les statistiques.
     *
     * MySQL reste la base principale.
     */
    if (
        $mongo !== null &&
        in_array(
            $nouveauStatut,
            ["acceptée", "terminée", "refusée"],
            true
        )
    ) {

        try {

            /**
             * Récupération des informations nécessaires
             * depuis la base MySQL.
             */
            $sqlMongo = "
                SELECT
                    c.numero_commande,
                    c.date_commande,
                    c.date_prestation,
                    c.heure_livraison,
                    c.prix_menu,
                    c.nombre_personne,
                    c.prix_livraison,
                    c.statut,

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
                    ON c.numero_commande = cu.numero_commande

                INNER JOIN utilisateur u
                    ON cu.utilisateur_id = u.utilisateur_id

                INNER JOIN commande_menu cm
                    ON c.numero_commande = cm.numero_commande

                INNER JOIN menu m
                    ON cm.menu_id = m.menu_id

                WHERE c.numero_commande = :numero_commande

                LIMIT 1
            ";

            $stmtMongo = $pdo->prepare($sqlMongo);

            $stmtMongo->execute([
                "numero_commande" => $numeroCommande
            ]);

            $commandeMongo =
                $stmtMongo->fetch(PDO::FETCH_ASSOC);


            if ($commandeMongo) {

                /**
                 * Document NoSQL.
                 */
                $document = [

                    "orderNumber" =>
                        $commandeMongo["numero_commande"],

                    "orderedAt" =>
                        $commandeMongo["date_commande"],

                    "prestationDate" =>
                        $commandeMongo["date_prestation"],

                    "status" =>
                        $commandeMongo["statut"],


                    "customer" => [

                        "userId" =>
                            (int) $commandeMongo["utilisateur_id"],

                        "firstName" =>
                            $commandeMongo["prenom"],

                        "email" =>
                            $commandeMongo["email"],

                        "phone" =>
                            $commandeMongo["telephone"]
                    ],


                    "menu" => [

                        "menuId" =>
                            (int) $commandeMongo["menu_id"],

                        "title" =>
                            $commandeMongo["menu_titre"],

                        "regime" =>
                            $commandeMongo["regime"]
                    ],


                    "order" => [

                        "nbPersons" =>
                            (int) $commandeMongo["nombre_personne"],

                        "menuPrice" =>
                            (float) $commandeMongo["prix_menu"],

                        "deliveryPrice" =>
                            (float) $commandeMongo["prix_livraison"],

                        "total" =>
                            (float) $commandeMongo["prix_menu"]
                            +
                            (float) $commandeMongo["prix_livraison"]
                    ],


                    "delivery" => [

                        "city" =>
                            $commandeMongo["ville"],

                        "time" =>
                            $commandeMongo["heure_livraison"]
                    ]
                ];


                /**
                 * Upsert MongoDB :
                 *
                 * Si la commande existe déjà :
                 * mise à jour.
                 *
                 * Sinon : création.
                 */
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
                    $mongoDatabase . ".orders_analytics",
                    $bulk
                );
            }

        } catch (Throwable $mongoErreur) {

            /**
             * Une erreur MongoDB ne doit pas
             * annuler la modification MySQL.
             */
            error_log(
                "Erreur synchronisation MongoDB : "
                . $mongoErreur->getMessage()
            );
        }
    }


    echo json_encode([
        "success" => true,
        "message" => "Statut de la commande modifié."
    ], JSON_UNESCAPED_UNICODE);


} catch (Throwable $e) {

    /**
     * Annulation de la transaction
     * si une erreur survient.
     */
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Erreur lors de la modification de la commande."
    ], JSON_UNESCAPED_UNICODE);
}
