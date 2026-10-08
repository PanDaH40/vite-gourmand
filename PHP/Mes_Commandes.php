<?php

session_start();

require __DIR__ . "/db.php";

header("Content-Type: application/json; charset=utf-8");


/**
 * L'utilisateur doit être connecté.
 */
if (!isset($_SESSION["utilisateur_id"])) {

    http_response_code(401);

    echo json_encode(
        ["error" => "Utilisateur non connecté."],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


$utilisateur_id = (int) $_SESSION["utilisateur_id"];


/**
 * Récupération uniquement des commandes
 * appartenant à l'utilisateur connecté.
 */
$sql = "
    SELECT
        commande.numero_commande,
        commande.date_commande,
        commande.date_prestation,
        commande.heure_livraison,
        commande.prix_menu,
        commande.prix_livraison,
        commande.nombre_personne,
        commande.statut,
        commande.pret_materiel,
        menu.titre

    FROM commande

    INNER JOIN commande_utilisateur
        ON commande.numero_commande =
           commande_utilisateur.numero_commande

    INNER JOIN commande_menu
        ON commande.numero_commande =
           commande_menu.numero_commande

    INNER JOIN menu
        ON commande_menu.menu_id =
           menu.menu_id

    WHERE commande_utilisateur.utilisateur_id =
          :utilisateur_id

    ORDER BY
        commande.date_commande DESC,
        commande.numero_commande DESC
";


$stmt = $pdo->prepare($sql);

$stmt->execute([
    "utilisateur_id" => $utilisateur_id
]);


$commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);


/**
 * Récupération de l'historique
 * de chaque commande.
 */
$sqlHistorique = "
    SELECT
        historique_commande.statut,
        historique_commande.date_modification

    FROM historique_commande

    WHERE historique_commande.numero_commande =
          :numero_commande

    ORDER BY
        historique_commande.date_modification ASC,
        historique_commande.historique_id ASC
";


$stmtHistorique = $pdo->prepare($sqlHistorique);


/**
 * Ajout de l'historique
 * dans chaque commande.
 */
foreach ($commandes as &$commande) {

    $stmtHistorique->execute([
        "numero_commande" =>
            $commande["numero_commande"]
    ]);

    $commande["historique"] =
        $stmtHistorique->fetchAll(
            PDO::FETCH_ASSOC
        );
}

unset($commande);


echo json_encode(
    $commandes,
    JSON_UNESCAPED_UNICODE
);