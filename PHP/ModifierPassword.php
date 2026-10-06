<?php

session_start();

require_once __DIR__ . "/db.php";

header("Content-Type: application/json; charset=utf-8");


if (!isset($_SESSION["utilisateur_id"])) {

    http_response_code(401);

    echo json_encode([
        "success" => false,
        "error" => "Utilisateur non connecté."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    http_response_code(405);

    echo json_encode([
        "success" => false,
        "error" => "Méthode non autorisée."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


$data = json_decode(
    file_get_contents("php://input"),
    true
);


if (!is_array($data)) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "error" => "Données invalides."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


$currentPassword = $data["current_password"] ?? "";
$newPassword = $data["new_password"] ?? "";
$confirmPassword = $data["confirm_password"] ?? "";


/*
|--------------------------------------------------------------------------
| Vérifications
|--------------------------------------------------------------------------
*/

if (
    $currentPassword === "" ||
    $newPassword === "" ||
    $confirmPassword === ""
) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "error" => "Tous les champs sont obligatoires."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


if ($newPassword !== $confirmPassword) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "error" => "Les deux nouveaux mots de passe ne correspondent pas."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


if (strlen($newPassword) < 8) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "error" => "Le nouveau mot de passe doit contenir au moins 8 caractères."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


try {

    /*
    |--------------------------------------------------------------------------
    | Récupération du mot de passe actuel
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT password

        FROM utilisateur

        WHERE utilisateur_id = :utilisateur_id

        LIMIT 1
    ");

    $stmt->execute([
        "utilisateur_id" => $_SESSION["utilisateur_id"]
    ]);

    $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);


    if (!$utilisateur) {

        http_response_code(404);

        echo json_encode([
            "success" => false,
            "error" => "Utilisateur introuvable."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Vérification de l'ancien mot de passe
    |--------------------------------------------------------------------------
    */

    if ($utilisateur["password"] !== md5($currentPassword)) {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "error" => "Le mot de passe actuel est incorrect."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Modification
    |--------------------------------------------------------------------------
    */

    $nouveauPasswordHash = md5($newPassword);


    $stmtUpdate = $pdo->prepare("
        UPDATE utilisateur

        SET password = :password

        WHERE utilisateur_id = :utilisateur_id
    ");


    $stmtUpdate->execute([
        "password" => $nouveauPasswordHash,
        "utilisateur_id" => $_SESSION["utilisateur_id"]
    ]);


    echo json_encode([
        "success" => true,
        "message" => "Mot de passe modifié avec succès."
    ], JSON_UNESCAPED_UNICODE);


} catch (PDOException $e) {

    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "error" => "Erreur lors de la modification du mot de passe."
    ], JSON_UNESCAPED_UNICODE);
}