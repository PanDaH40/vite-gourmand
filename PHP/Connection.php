<?php

// Sécurisation des cookies de session
ini_set("session.use_strict_mode", "1");
ini_set("session.use_only_cookies", "1");

session_set_cookie_params([
    "lifetime" => 0,
    "path" => "/",
    "secure" => !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off",
    "httponly" => true,
    "samesite" => "Lax"
]);

session_start();

require __DIR__ . "/db.php";
require __DIR__ . "/PasswordSecurity.php";

// Refuser les autres méthodes HTTP
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Méthode non autorisée.");
}

// Récupération sécurisée des champs
$email = $_POST["email"] ?? "";
$password = $_POST["password"] ?? "";

if (!is_string($email) || !is_string($password)) {
    http_response_code(400);
    exit("Données invalides.");
}

$email = trim($email);

if ($email === "" || $password === "") {
    http_response_code(400);
    exit("Veuillez remplir tous les champs.");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    exit("Adresse email invalide.");
}

try {

    // Rechercher l'utilisateur dans MySQL
    $sql = "
        SELECT utilisateur_id, email, prenom, password
        FROM utilisateur
        WHERE email = :email
        LIMIT 1
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        "email" => $email
    ]);

    $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

    // Message identique pour ne pas révéler si l'email existe
    if (!$utilisateur) {
        http_response_code(401);
        exit("Email ou mot de passe incorrect.");
    }

    $hashStocke = (string) $utilisateur["password"];

    $motDePasseValide = false;
    $ancienFormat = false;

    // Vérification du nouveau format PBKDF2
    if (
        strlen($hashStocke) === 49 &&
        str_starts_with($hashStocke, PASSWORD_PREFIX)
    ) {

        $motDePasseValide = verifierHashPassword(
            $password,
            $hashStocke
        );

    }

    // Vérification des anciens comptes MD5
    elseif (
        strlen($hashStocke) === 32 &&
        ctype_xdigit($hashStocke)
    ) {

        $motDePasseValide = hash_equals(
            strtolower($hashStocke),
            md5($password)
        );

        $ancienFormat = $motDePasseValide;
    }

    if (!$motDePasseValide) {
        http_response_code(401);
        exit("Email ou mot de passe incorrect.");
    }

    // Migration automatique des anciens comptes MD5
    if ($ancienFormat) {

        $nouveauHash = creerHashPassword($password);

        $stmtUpdate = $pdo->prepare("
            UPDATE utilisateur
            SET password = :nouveau_password
            WHERE utilisateur_id = :utilisateur_id
              AND password = :ancien_password
        ");

        $stmtUpdate->execute([
            "nouveau_password" => $nouveauHash,
            "utilisateur_id" => $utilisateur["utilisateur_id"],
            "ancien_password" => $hashStocke
        ]);
    }

    // Renouveler l'identifiant de session
    session_regenerate_id(true);

    $_SESSION["utilisateur_id"] = $utilisateur["utilisateur_id"];
    $_SESSION["email"] = $utilisateur["email"];
    $_SESSION["prenom"] = $utilisateur["prenom"];

    // Redirection après connexion
    header("Location: ../PagePrincipale.html", true, 303);
    exit;

} catch (Throwable $e) {

    // Journalisation côté serveur uniquement
    error_log("Erreur connexion : " . $e->getMessage());

    http_response_code(500);
    exit("Une erreur est survenue. Veuillez réessayer.");
}
