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


$prenom = trim($data["prenom"] ?? "");
$email = trim($data["email"] ?? "");
$telephone = trim($data["telephone"] ?? "");
$adressePostale = trim($data["adresse_postale"] ?? "");
$ville = trim($data["ville"] ?? "");
$pays = trim($data["pays"] ?? "");


/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

if ($prenom === "" || $email === "") {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "error" => "Le prénom et l'adresse mail sont obligatoires."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "error" => "L'adresse mail n'est pas valide."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/*
|--------------------------------------------------------------------------
| Respect des VARCHAR(50) de la base
|--------------------------------------------------------------------------
*/

$champs = [
    $prenom,
    $email,
    $telephone,
    $adressePostale,
    $ville,
    $pays
];

foreach ($champs as $champ) {

    if (mb_strlen($champ) > 50) {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "error" => "Un des champs dépasse 50 caractères."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }
}


try {

    /*
    |--------------------------------------------------------------------------
    | Vérifier si l'email appartient déjà à un autre utilisateur
    |--------------------------------------------------------------------------
    */

    $stmtEmail = $pdo->prepare("
        SELECT utilisateur_id

        FROM utilisateur

        WHERE email = :email
          AND utilisateur_id != :utilisateur_id

        LIMIT 1
    ");

    $stmtEmail->execute([
        "email" => $email,
        "utilisateur_id" => $_SESSION["utilisateur_id"]
    ]);


    if ($stmtEmail->fetch()) {

        http_response_code(409);

        echo json_encode([
            "success" => false,
            "error" => "Cette adresse mail est déjà utilisée."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Modification
    |--------------------------------------------------------------------------
    */

    $sql = "
        UPDATE utilisateur

        SET
            prenom = :prenom,
            email = :email,
            telephone = :telephone,
            adresse_postale = :adresse_postale,
            ville = :ville,
            pays = :pays

        WHERE utilisateur_id = :utilisateur_id
    ";


    $stmt = $pdo->prepare($sql);


    $stmt->execute([

        "prenom" => $prenom,

        "email" => $email,

        "telephone" => $telephone,

        "adresse_postale" => $adressePostale,

        "ville" => $ville,

        "pays" => $pays,

        "utilisateur_id" => $_SESSION["utilisateur_id"]
    ]);


    echo json_encode([
        "success" => true,
        "message" => "Profil modifié avec succès."
    ], JSON_UNESCAPED_UNICODE);


} catch (PDOException $e) {

    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "error" => "Erreur lors de la modification du profil."
    ], JSON_UNESCAPED_UNICODE);
}