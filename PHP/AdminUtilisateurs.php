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
      AND role.libelle = 'Administrateur'
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
| Récupération des utilisateurs
|--------------------------------------------------------------------------
*/

try {

    $sql = "
        SELECT
            utilisateur.utilisateur_id,
            utilisateur.prenom,
            utilisateur.email,
            utilisateur.telephone,
            utilisateur.ville,
            utilisateur.pays,
            utilisateur.adresse_postale,
            utilisateur.actif,
            role.libelle AS role

        FROM utilisateur

        LEFT JOIN possede_utilisateur_role
            ON utilisateur.utilisateur_id =
               possede_utilisateur_role.utilisateur_id

        LEFT JOIN role
            ON possede_utilisateur_role.role_id =
               role.role_id

        ORDER BY utilisateur.utilisateur_id ASC
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute();

    $utilisateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);


    /*
    |--------------------------------------------------------------------------
    | Réponse JSON
    |--------------------------------------------------------------------------
    */

    echo json_encode([
        "success" => true,
        "utilisateurs" => $utilisateurs
    ], JSON_UNESCAPED_UNICODE);


} catch (PDOException $e) {

    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "error" => "Erreur lors de la récupération des utilisateurs."
    ], JSON_UNESCAPED_UNICODE);
}