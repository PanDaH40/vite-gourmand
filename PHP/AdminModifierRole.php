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

$sqlRoleAdmin = "
    SELECT role.libelle
    FROM possede_utilisateur_role

    INNER JOIN role
        ON possede_utilisateur_role.role_id = role.role_id

    WHERE possede_utilisateur_role.utilisateur_id = :utilisateur_id
    AND role.libelle = 'Administrateur'
    LIMIT 1
";

$stmtRoleAdmin = $pdo->prepare($sqlRoleAdmin);

$stmtRoleAdmin->execute([
    "utilisateur_id" => $_SESSION["utilisateur_id"]
]);

$roleAdmin = $stmtRoleAdmin->fetch(PDO::FETCH_ASSOC);


if (
    !$roleAdmin ||
    $roleAdmin["libelle"] !== "Administrateur"
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
| Méthode POST obligatoire
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

$nouveauRole = trim(
    $data["role"] ?? ""
);


/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

$rolesAutorises = [
    "Utilisateur",
    "Employé"
];


if (
    $utilisateurId <= 0 ||
    !in_array($nouveauRole, $rolesAutorises, true)
) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "error" => "Utilisateur ou rôle invalide."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/*
|--------------------------------------------------------------------------
| Empêcher l'administrateur de retirer son propre rôle
|--------------------------------------------------------------------------
*/

if (
    $utilisateurId === (int) $_SESSION["utilisateur_id"]
) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "error" => "Vous ne pouvez pas modifier votre propre rôle administrateur."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


try {

    /*
    |--------------------------------------------------------------------------
    | Vérifier que l'utilisateur existe
    |--------------------------------------------------------------------------
    */

    $stmtUtilisateur = $pdo->prepare("
        SELECT utilisateur_id
        FROM utilisateur
        WHERE utilisateur_id = :utilisateur_id
        LIMIT 1
    ");

    $stmtUtilisateur->execute([
        "utilisateur_id" => $utilisateurId
    ]);


    if (!$stmtUtilisateur->fetch()) {

        http_response_code(404);

        echo json_encode([
            "success" => false,
            "error" => "Utilisateur introuvable."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Récupérer l'identifiant du nouveau rôle
    |--------------------------------------------------------------------------
    */

    $stmtRole = $pdo->prepare("
        SELECT role_id
        FROM role
        WHERE libelle = :libelle
        LIMIT 1
    ");

    $stmtRole->execute([
        "libelle" => $nouveauRole
    ]);

    $role = $stmtRole->fetch(PDO::FETCH_ASSOC);


    if (!$role) {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "error" => "Le rôle demandé n'existe pas."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Ajout ou modification du rôle
    |--------------------------------------------------------------------------
    |
    | utilisateur_id est la clé primaire de possede_utilisateur_role.
    |
    | Si l'utilisateur n'a encore aucun rôle :
    |     -> INSERT
    |
    | S'il possède déjà un rôle :
    |     -> UPDATE
    |--------------------------------------------------------------------------
    */

    $stmtRoleExistant = $pdo->prepare("
        SELECT utilisateur_id
        FROM possede_utilisateur_role
        WHERE utilisateur_id = :utilisateur_id
        LIMIT 1
    ");

    $stmtRoleExistant->execute([
        "utilisateur_id" => $utilisateurId
    ]);


    if ($stmtRoleExistant->fetch()) {

        /*
         * Rôle existant :
         * on le modifie.
         */

        $stmtUpdate = $pdo->prepare("
            UPDATE possede_utilisateur_role
            SET role_id = :role_id
            WHERE utilisateur_id = :utilisateur_id
        ");

        $stmtUpdate->execute([
            "role_id" => $role["role_id"],
            "utilisateur_id" => $utilisateurId
        ]);

    } else {

        /*
         * Aucun rôle :
         * on crée la relation.
         */

        $stmtInsert = $pdo->prepare("
            INSERT INTO possede_utilisateur_role
            (
                utilisateur_id,
                role_id
            )
            VALUES
            (
                :utilisateur_id,
                :role_id
            )
        ");

        $stmtInsert->execute([
            "utilisateur_id" => $utilisateurId,
            "role_id" => $role["role_id"]
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Réponse
    |--------------------------------------------------------------------------
    */

    echo json_encode([
        "success" => true,
        "message" => "Rôle modifié avec succès.",
        "role" => $nouveauRole
    ], JSON_UNESCAPED_UNICODE);


} catch (PDOException $e) {

    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "error" => "Erreur lors de la modification du rôle."
    ], JSON_UNESCAPED_UNICODE);
}