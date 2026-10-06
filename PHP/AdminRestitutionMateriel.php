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


/*
|--------------------------------------------------------------------------
| Vérification du rôle administrateur
|--------------------------------------------------------------------------
*/

$sqlRole = "
    SELECT role.libelle
    FROM possede_utilisateur_role

    INNER JOIN role
        ON possede_utilisateur_role.role_id = role.role_id

    WHERE possede_utilisateur_role.utilisateur_id = :utilisateur_id

    LIMIT 1
";

$stmtRole = $pdo->prepare($sqlRole);

$stmtRole->execute([
    "utilisateur_id" => $_SESSION["utilisateur_id"]
]);

$role = $stmtRole->fetch(PDO::FETCH_ASSOC);


if (!$role || $role["libelle"] !== "Administrateur") {

    http_response_code(403);

    echo json_encode([
        "success" => false,
        "error" => "Accès administrateur refusé."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/*
|--------------------------------------------------------------------------
| POST uniquement
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    http_response_code(405);

    echo json_encode([
        "success" => false,
        "error" => "Méthode non autorisée."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/*
|--------------------------------------------------------------------------
| Lecture du JSON
|--------------------------------------------------------------------------
*/

$data = json_decode(
    file_get_contents("php://input"),
    true
);

$numeroCommande = trim(
    $data["numero_commande"] ?? ""
);


if ($numeroCommande === "") {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "error" => "Numéro de commande manquant."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


try {

    /*
    |--------------------------------------------------------------------------
    | Vérification de la commande
    |--------------------------------------------------------------------------
    */

    $stmtCommande = $pdo->prepare("
        SELECT
            numero_commande,
            pret_materiel,
            restitution_materiel

        FROM commande

        WHERE numero_commande = :numero_commande

        LIMIT 1
    ");

    $stmtCommande->execute([
        "numero_commande" => $numeroCommande
    ]);

    $commande = $stmtCommande->fetch(PDO::FETCH_ASSOC);


    if (!$commande) {

        http_response_code(404);

        echo json_encode([
            "success" => false,
            "error" => "Commande introuvable."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Vérification du prêt
    |--------------------------------------------------------------------------
    */

    if ((int) $commande["pret_materiel"] !== 1) {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "error" => "Cette commande ne possède pas de prêt de matériel."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Vérification si déjà restitué
    |--------------------------------------------------------------------------
    */

    if ((int) $commande["restitution_materiel"] === 1) {

        echo json_encode([
            "success" => true,
            "message" => "Le matériel a déjà été restitué."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Enregistrement de la restitution
    |--------------------------------------------------------------------------
    */

    $stmtUpdate = $pdo->prepare("
        UPDATE commande

        SET restitution_materiel = 1

        WHERE numero_commande = :numero_commande
          AND pret_materiel = 1
    ");

    $stmtUpdate->execute([
        "numero_commande" => $numeroCommande
    ]);


    echo json_encode([
        "success" => true,
        "message" => "Restitution enregistrée avec succès."
    ], JSON_UNESCAPED_UNICODE);


} catch (PDOException $e) {

    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "error" => "Erreur lors de l'enregistrement de la restitution."
    ], JSON_UNESCAPED_UNICODE);
}