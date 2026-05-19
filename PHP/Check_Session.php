<?php
session_start();

require __DIR__ . "/db.php";

header("Content-Type: application/json");

if (!isset($_SESSION["utilisateur_id"])) {

    echo json_encode([
        "connecte" => false
    ]);

    exit;
}

$utilisateur_id = $_SESSION["utilisateur_id"];

$sql = "SELECT role.libelle
FROM possede_utilisateur_role

INNER JOIN role
ON possede_utilisateur_role.role_id = role.role_id

WHERE possede_utilisateur_role.utilisateur_id = :utilisateur_id

LIMIT 1";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    "utilisateur_id" => $utilisateur_id
]);

$role = $stmt->fetch(PDO::FETCH_ASSOC);

$admin = false;

if ($role && $role["libelle"] === "Administrateur") {
    $admin = true;
}

echo json_encode([
    "connecte" => true,
    "prenom" => $_SESSION["prenom"],
    "admin" => $admin
]);