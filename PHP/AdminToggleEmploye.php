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
| Vérification du rôle Administrateur
|--------------------------------------------------------------------------
*/

$stmtAdmin = $pdo->prepare("
    SELECT role.libelle
    FROM possede_utilisateur_role

    INNER JOIN role
        ON role.role_id = possede_utilisateur_role.role_id

    WHERE possede_utilisateur_role.utilisateur_id = :utilisateur_id
    AND role.libelle = 'Administrateur'

    LIMIT 1
");

$stmtAdmin->execute([
    "utilisateur_id" => $_SESSION["utilisateur_id"]
]);

if (!$stmtAdmin->fetch(PDO::FETCH_ASSOC)) {

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


$utilisateurId = (int) ($data["utilisateur_id"] ?? 0);
$actif = (int) ($data["actif"] ?? -1);


if (
    $utilisateurId <= 0 ||
    !in_array($actif, [0, 1], true)
) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "error" => "Données invalides."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


try {

    /*
    |--------------------------------------------------------------------------
    | Vérifier qu'il s'agit bien d'un Employé
    |--------------------------------------------------------------------------
    */

    $stmtEmploye = $pdo->prepare("
        SELECT utilisateur.utilisateur_id

        FROM utilisateur

        INNER JOIN possede_utilisateur_role
            ON utilisateur.utilisateur_id =
               possede_utilisateur_role.utilisateur_id

        INNER JOIN role
            ON possede_utilisateur_role.role_id =
               role.role_id

        WHERE utilisateur.utilisateur_id = :utilisateur_id
        AND role.libelle = 'Employé'

        LIMIT 1
    ");

    $stmtEmploye->execute([
        "utilisateur_id" => $utilisateurId
    ]);


    if (!$stmtEmploye->fetch(PDO::FETCH_ASSOC)) {

        http_response_code(404);

        echo json_encode([
            "success" => false,
            "error" => "Compte employé introuvable."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Activation / désactivation
    |--------------------------------------------------------------------------
    */

    $stmtUpdate = $pdo->prepare("
        UPDATE utilisateur
        SET actif = :actif
        WHERE utilisateur_id = :utilisateur_id
    ");

    $stmtUpdate->execute([
        "actif" => $actif,
        "utilisateur_id" => $utilisateurId
    ]);


    echo json_encode([
        "success" => true,
        "message" =>
            $actif === 1
                ? "Compte employé réactivé."
                : "Compte employé désactivé."
    ], JSON_UNESCAPED_UNICODE);


} catch (PDOException $e) {

    error_log(
        "Erreur statut employé : " .
        $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "error" => "Erreur lors de la modification du compte employé."
    ], JSON_UNESCAPED_UNICODE);
}