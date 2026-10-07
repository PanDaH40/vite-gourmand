<?php

session_start();

require __DIR__ . "/db.php";

header("Content-Type: application/json; charset=utf-8");


/*
 * Vérification de la connexion.
 */
if (!isset($_SESSION["utilisateur_id"])) {

    http_response_code(401);

    echo json_encode(
        ["erreur" => "Non connecté"],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

$utilisateur_id = (int) $_SESSION["utilisateur_id"];


/*
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
    "utilisateur_id" => $utilisateur_id
]);

$role = $stmtRole->fetch(PDO::FETCH_ASSOC);


if (!$role || $role["libelle"] !== "Administrateur") {

    http_response_code(403);

    echo json_encode(
        ["erreur" => "Accès refusé"],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


/*
 * Récupération de toutes les commandes.
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
        commande.restitution_materiel,

        utilisateur.utilisateur_id,
        utilisateur.prenom,
        utilisateur.email,

        menu.menu_id,
        menu.titre

    FROM commande

    INNER JOIN commande_utilisateur
        ON commande.numero_commande =
           commande_utilisateur.numero_commande

    INNER JOIN utilisateur
        ON commande_utilisateur.utilisateur_id =
           utilisateur.utilisateur_id

    INNER JOIN commande_menu
        ON commande.numero_commande =
           commande_menu.numero_commande

    INNER JOIN menu
        ON commande_menu.menu_id =
           menu.menu_id

    ORDER BY
        commande.date_commande DESC,
        commande.numero_commande DESC
";


$stmt = $pdo->query($sql);

$commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);


echo json_encode(
    $commandes,
    JSON_UNESCAPED_UNICODE
);