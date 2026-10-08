<?php

session_start();

require_once __DIR__ . "/db.php";
require_once __DIR__ . "/PasswordSecurity.php";
require_once __DIR__ . "/Mailer.php";

header("Content-Type: application/json; charset=utf-8");


/*
|--------------------------------------------------------------------------
| Administrateur connecté
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
| Lecture des données
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

$email = trim($data["email"] ?? "");
$password = $data["password"] ?? "";


/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "error" => "Adresse email invalide."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


if (
    strlen($password) < 10 ||
    !preg_match('/[A-Z]/', $password) ||
    !preg_match('/[a-z]/', $password) ||
    !preg_match('/[0-9]/', $password) ||
    !preg_match('/[^a-zA-Z0-9]/', $password)
) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "error" =>
            "Le mot de passe doit contenir au moins " .
            "10 caractères, une majuscule, une minuscule, " .
            "un chiffre et un caractère spécial."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


try {

    /*
    |--------------------------------------------------------------------------
    | Vérifier si l'email existe
    |--------------------------------------------------------------------------
    */

    $stmtExiste = $pdo->prepare("
        SELECT utilisateur_id

        FROM utilisateur

        WHERE email = :email

        LIMIT 1
    ");

    $stmtExiste->execute([
        "email" => $email
    ]);

    if ($stmtExiste->fetch(PDO::FETCH_ASSOC)) {

        http_response_code(409);

        echo json_encode([
            "success" => false,
            "error" => "Cette adresse email est déjà utilisée."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Récupérer le rôle Employé
    |--------------------------------------------------------------------------
    */

    $stmtRole = $pdo->prepare("
        SELECT role_id

        FROM role

        WHERE libelle = 'Employé'

        LIMIT 1
    ");

    $stmtRole->execute();

    $roleEmploye = $stmtRole->fetch(PDO::FETCH_ASSOC);

    if (!$roleEmploye) {

        throw new Exception(
            "Le rôle Employé n'existe pas dans la base de données."
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Hash du mot de passe
    |--------------------------------------------------------------------------
    */

    $passwordHash = creerHashPassword($password);


    /*
    |--------------------------------------------------------------------------
    | Création utilisateur + rôle
    |--------------------------------------------------------------------------
    */

    $pdo->beginTransaction();


    $stmtUtilisateur = $pdo->prepare("
        INSERT INTO utilisateur
        (
            email,
            password
        )
        VALUES
        (
            :email,
            :password
        )
    ");

    $stmtUtilisateur->execute([
        "email" => $email,
        "password" => $passwordHash
    ]);


    $utilisateurId = (int) $pdo->lastInsertId();


    $stmtLiaison = $pdo->prepare("
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

    $stmtLiaison->execute([
        "utilisateur_id" => $utilisateurId,
        "role_id" => $roleEmploye["role_id"]
    ]);


    $pdo->commit();


    /*
    |--------------------------------------------------------------------------
    | Email de notification
    |--------------------------------------------------------------------------
    | IMPORTANT :
    | le mot de passe n'est volontairement jamais envoyé par email.
    |--------------------------------------------------------------------------
    */

    $contenuHtml = "
        <h1>Votre compte employé Vite & Gourmand</h1>

        <p>
            Un compte employé Vite & Gourmand vient d'être créé
            avec cette adresse email.
        </p>

        <p>
            Vous pouvez désormais vous connecter à l'application
            avec les identifiants qui vous ont été communiqués.
        </p>

        <p>
            Pour des raisons de sécurité, votre mot de passe
            n'est pas communiqué dans cet email.
        </p>
    ";


    $contenuTexte =
        "Votre compte employé Vite & Gourmand vient d'être créé.\n\n" .
        "Vous pouvez désormais vous connecter à l'application " .
        "avec les identifiants qui vous ont été communiqués.\n\n" .
        "Pour des raisons de sécurité, votre mot de passe " .
        "n'est pas communiqué dans cet email.";


    envoyerEmail(
        $email,
        "Création de votre compte employé - Vite & Gourmand",
        $contenuHtml,
        $contenuTexte
    );


    echo json_encode([
        "success" => true,
        "message" => "Compte employé créé avec succès."
    ], JSON_UNESCAPED_UNICODE);


} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log(
        "Erreur création employé : " .
        $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "error" => "Erreur lors de la création du compte employé."
    ], JSON_UNESCAPED_UNICODE);
}