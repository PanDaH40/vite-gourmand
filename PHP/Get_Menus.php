<?php
require __DIR__ . "/db.php";

header("Content-Type: application/json");

$sql = "SELECT menu_id, titre, nombre_personne_minimum, prix_par_personne, regime, description, quantite_restante
        FROM menu
        ORDER BY menu_id DESC";

$stmt = $pdo->query($sql);
$menus = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($menus);