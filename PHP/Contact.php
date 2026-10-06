<?php

header("Content-Type: application/json; charset=utf-8");

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


$email =
    trim($data["email"] ?? "");

$titre =
    trim($data["titre"] ?? "");

$message =
    trim($data["message"] ?? "");


/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

if (
    $email === "" ||
    $titre === "" ||
    $message === ""
) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "error" => "Tous les champs sont obligatoires."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "error" => "L'adresse email n'est pas valide."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


if (mb_strlen($titre) > 100) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "error" => "Le titre est trop long."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


if (mb_strlen($message) > 2000) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "error" => "Le message est trop long."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/*
|--------------------------------------------------------------------------
| Traitement
|--------------------------------------------------------------------------
|
| Aucun serveur SMTP n'est actuellement configuré dans l'environnement
| Docker. Les données sont donc validées côté serveur.
|
| L'envoi réel pourra être configuré lors du déploiement.
|
*/

echo json_encode([
    "success" => true,
    "message" => "Votre demande a bien été prise en compte."
], JSON_UNESCAPED_UNICODE);