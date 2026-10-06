<?php

session_start();

require_once __DIR__ . "/db.php";

header("Content-Type: application/json; charset=utf-8");


/*
|--------------------------------------------------------------------------
| Vérification de la connexion
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["utilisateur_id"])) {

    http_response_code(401);

    echo json_encode([
        "success" => false,
        "error" => "Utilisateur non connecté."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


try {

    /*
    |--------------------------------------------------------------------------
    | Récupération de l'utilisateur connecté
    |--------------------------------------------------------------------------
    */

    $sql = "
        SELECT
            utilisateur_id,
            prenom,
            email,
            telephone,
            adresse_postale,
            ville,
            pays

        FROM utilisateur

        WHERE utilisateur_id = :utilisateur_id

        LIMIT 1
    ";

    $stmt = $pdo->prepare($sql);

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
    | Réponse
    |--------------------------------------------------------------------------
    */

    echo json_encode([
        "success" => true,
        "utilisateur" => $utilisateur
    ], JSON_UNESCAPED_UNICODE);


} catch (PDOException $e) {

    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "error" => "Erreur lors de la récupération du profil."
    ], JSON_UNESCAPED_UNICODE);
}