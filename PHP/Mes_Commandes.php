<?php
session_start();

require __DIR__ . "/db.php";

header("Content-Type: application/json");

if (!isset($_SESSION["utilisateur_id"])) {
    echo json_encode([]);
    exit;
}

$utilisateur_id = $_SESSION["utilisateur_id"];

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
    menu.titre

FROM commande

INNER JOIN commande_utilisateur
ON commande.numero_commande = commande_utilisateur.numero_commande

INNER JOIN commande_menu
ON commande.numero_commande = commande_menu.numero_commande

INNER JOIN menu
ON commande_menu.menu_id = menu.menu_id

WHERE commande_utilisateur.utilisateur_id = :utilisateur_id

ORDER BY commande.date_commande DESC";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    "utilisateur_id" => $utilisateur_id
]);

$commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($commandes);