<?php
session_start();

require __DIR__ . "/db.php";

header("Content-Type: application/json");

if (!isset($_SESSION["utilisateur_id"])) {
    echo json_encode(["erreur" => "Non connecté"]);
    exit;
}

$sql = "SELECT role.libelle
FROM possede_utilisateur_role
INNER JOIN role ON possede_utilisateur_role.role_id = role.role_id
WHERE possede_utilisateur_role.utilisateur_id = :utilisateur_id
LIMIT 1";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    "utilisateur_id" => $_SESSION["utilisateur_id"]
]);

$role = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$role || $role["libelle"] !== "Administrateur") {
    echo json_encode(["erreur" => "Accès refusé"]);
    exit;
}

$stats = [];

$stats["commandes_attente"] = $pdo
    ->query("SELECT COUNT(*) FROM commande WHERE statut = 'en attente'")
    ->fetchColumn();

$stats["commandes_acceptees"] = $pdo
    ->query("SELECT COUNT(*) FROM commande WHERE statut = 'acceptée'")
    ->fetchColumn();

$stats["menus_disponibles"] = $pdo
    ->query("SELECT COUNT(*) FROM menu WHERE quantite_restante > 0")
    ->fetchColumn();

$stats["materiel_a_recuperer"] = $pdo
    ->query("SELECT COUNT(*) FROM commande WHERE pret_materiel = 1 AND restitution_materiel = 0")
    ->fetchColumn();

echo json_encode($stats);