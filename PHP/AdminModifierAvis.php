<?php

session_start();

require_once __DIR__ . "/db.php";

header("Content-Type: application/json; charset=utf-8");


/*
|--------------------------------------------------------------------------
| Connexion
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
| Vérification administrateur
|--------------------------------------------------------------------------
*/

$sqlRole = "
    SELECT role.libelle

    FROM possede_utilisateur_role

    INNER JOIN role
        ON possede_utilisateur_role.role_id =
           role.role_id

    WHERE possede_utilisateur_role.utilisateur_id =
          :utilisateur_id

    LIMIT 1
";


$stmtRole = $pdo->prepare($sqlRole);

$stmtRole->execute([
    "utilisateur_id" =>
        $_SESSION["utilisateur_id"]
]);

$role =
    $stmtRole->fetch(PDO::FETCH_ASSOC);


if (
    !$role ||
    $role["libelle"] !== "Administrateur"
) {

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


if (!is_array($data)) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "error" => "Données invalides."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


$avisId =
    (int) ($data["avis_id"] ?? 0);

$statut =
    trim($data["statut"] ?? "");


/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

$statutsAutorises = [
    "Accepté",
    "Refusé"
];


if (
    $avisId <= 0 ||
    !in_array(
        $statut,
        $statutsAutorises,
        true
    )
) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "error" => "Avis ou statut invalide."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


try {

    /*
    |--------------------------------------------------------------------------
    | Vérifier l'existence de l'avis
    |--------------------------------------------------------------------------
    */

    $stmtAvis = $pdo->prepare("
        SELECT avis_id

        FROM avis

        WHERE avis_id = :avis_id

        LIMIT 1
    ");


    $stmtAvis->execute([
        "avis_id" => $avisId
    ]);


    if (!$stmtAvis->fetch()) {

        http_response_code(404);

        echo json_encode([
            "success" => false,
            "error" => "Avis introuvable."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Modification
    |--------------------------------------------------------------------------
    */

    $stmtUpdate = $pdo->prepare("
        UPDATE avis

        SET statut = :statut

        WHERE avis_id = :avis_id
    ");


    $stmtUpdate->execute([

        "statut" => $statut,

        "avis_id" => $avisId
    ]);


    echo json_encode([
        "success" => true,
        "message" => "Statut de l'avis modifié."
    ], JSON_UNESCAPED_UNICODE);


} catch (PDOException $e) {

    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "error" => "Erreur lors de la modification de l'avis."
    ], JSON_UNESCAPED_UNICODE);
}