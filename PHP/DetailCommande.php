<?php
session_start();

require __DIR__ . "/db.php";

header("Content-Type: application/json");

if (!isset($_SESSION["utilisateur_id"])) {
    echo json_encode([
        "erreur" => "Utilisateur non connecté"
    ]);
    exit;
}

if (!isset($_GET["numero"])) {
    echo json_encode([
        "erreur" => "Numéro de commande manquant"
    ]);
    exit;
}

$utilisateur_id = $_SESSION["utilisateur_id"];
$numero_commande = $_GET["numero"];

$sql = "SELECT
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
    menu.titre

FROM commande

INNER JOIN commande_utilisateur
ON commande.numero_commande = commande_utilisateur.numero_commande

INNER JOIN commande_menu
ON commande.numero_commande = commande_menu.numero_commande

INNER JOIN menu
ON commande_menu.menu_id = menu.menu_id

WHERE commande.numero_commande = :numero_commande
AND commande_utilisateur.utilisateur_id = :utilisateur_id

LIMIT 1";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    "numero_commande" => $numero_commande,
    "utilisateur_id" => $utilisateur_id
]);

$commande = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$commande) {
    echo json_encode([
        "erreur" => "Commande introuvable"
    ]);
    exit;
}

echo json_encode($commande);
?>