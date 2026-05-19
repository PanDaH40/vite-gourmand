<?php
session_start();

require "db.php";

header("Content-Type: application/json");

if (!isset($_SESSION["utilisateur_id"])) {

    echo json_encode([
        "admin" => false
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

if ($role && $role["libelle"] === "Administrateur") {

    echo json_encode([
        "admin" => true
    ]);

} else {

    echo json_encode([
        "admin" => false
    ]);
}