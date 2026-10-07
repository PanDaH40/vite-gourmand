<?php
session_start();

require_once __DIR__ . "/db.php";
require_once __DIR__ . "/PasswordSecurity.php";

header("Content-Type: application/json; charset=utf-8");
header("Cache-Control: no-store");

// Fonction pour envoyer les réponses JSON
function envoyerReponse(int $code, bool $success, string $message): void
{
    http_response_code($code);

    echo json_encode(
        $success
            ? [
                "success" => true,
                "message" => $message
            ]
            : [
                "success" => false,
                "error" => $message
            ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

// Vérifier que l'utilisateur est connecté
if (empty($_SESSION["utilisateur_id"])) {
    envoyerReponse(401, false, "Utilisateur non connecté.");
}

// Vérifier la méthode HTTP
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    envoyerReponse(405, false, "Méthode non autorisée.");
}

// Récupérer les données JSON
$data = json_decode(
    file_get_contents("php://input"),
    true
);

if (!is_array($data)) {
    envoyerReponse(400, false, "Données invalides.");
}

// Vérifier le jeton CSRF
$csrfToken = $data["csrf_token"] ?? "";

if (
    !is_string($csrfToken) ||
    empty($_SESSION["csrf_token"]) ||
    !hash_equals($_SESSION["csrf_token"], $csrfToken)
) {
    envoyerReponse(403, false, "Jeton de sécurité invalide.");
}

// Récupérer les mots de passe
$currentPassword = $data["current_password"] ?? "";
$newPassword = $data["new_password"] ?? "";
$confirmPassword = $data["confirm_password"] ?? "";

// Vérifier les types
if (
    !is_string($currentPassword) ||
    !is_string($newPassword) ||
    !is_string($confirmPassword)
) {
    envoyerReponse(400, false, "Données invalides.");
}

// Vérifier les champs obligatoires
if (
    $currentPassword === "" ||
    $newPassword === "" ||
    $confirmPassword === ""
) {
    envoyerReponse(400, false, "Tous les champs sont obligatoires.");
}

// Vérifier la confirmation
if ($newPassword !== $confirmPassword) {
    envoyerReponse(
        400,
        false,
        "Les deux nouveaux mots de passe ne correspondent pas."
    );
}

// Vérifier la complexité du nouveau mot de passe
if (
    strlen($newPassword) < 10 ||
    !preg_match('/[A-Z]/', $newPassword) ||
    !preg_match('/[a-z]/', $newPassword) ||
    !preg_match('/[0-9]/', $newPassword) ||
    !preg_match('/[^A-Za-z0-9]/', $newPassword)
) {
    envoyerReponse(
        400,
        false,
        "Le mot de passe doit contenir au moins 10 caractères, "
        . "une majuscule, une minuscule, un chiffre "
        . "et un caractère spécial."
    );
}

try {

    // Récupérer le mot de passe actuel
    $stmt = $pdo->prepare("
        SELECT password
        FROM utilisateur
        WHERE utilisateur_id = :utilisateur_id
        LIMIT 1
    ");

    $stmt->execute([
        "utilisateur_id" => $_SESSION["utilisateur_id"]
    ]);

    $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$utilisateur) {
        envoyerReponse(404, false, "Utilisateur introuvable.");
    }

    $hashStocke = (string) $utilisateur["password"];
    $motDePasseValide = false;

    // Vérification PBKDF2
    if (
        strlen($hashStocke) === 49 &&
        str_starts_with($hashStocke, PASSWORD_PREFIX)
    ) {
        $motDePasseValide = verifierHashPassword(
            $currentPassword,
            $hashStocke
        );
    }

    // Compatibilité avec les anciens mots de passe MD5
    elseif (
        strlen($hashStocke) === 32 &&
        ctype_xdigit($hashStocke)
    ) {
        $motDePasseValide = hash_equals(
            strtolower($hashStocke),
            md5($currentPassword)
        );
    }

    if (!$motDePasseValide) {
        envoyerReponse(
            400,
            false,
            "Le mot de passe actuel est incorrect."
        );
    }

    // Créer le nouveau hash PBKDF2
    $nouveauPasswordHash = creerHashPassword($newPassword);

    // Mettre à jour le mot de passe
    $stmtUpdate = $pdo->prepare("
        UPDATE utilisateur
        SET password = :password
        WHERE utilisateur_id = :utilisateur_id
          AND password = :ancien_password
    ");

    $stmtUpdate->execute([
        "password" => $nouveauPasswordHash,
        "utilisateur_id" => $_SESSION["utilisateur_id"],
        "ancien_password" => $hashStocke
    ]);

    if ($stmtUpdate->rowCount() !== 1) {
        envoyerReponse(
            409,
            false,
            "Le mot de passe a été modifié entre-temps. Réessayez."
        );
    }

    envoyerReponse(
        200,
        true,
        "Mot de passe modifié avec succès."
    );

} catch (Throwable $e) {

    error_log("Erreur ModifierPassword : " . $e->getMessage());

    envoyerReponse(
        500,
        false,
        "Erreur lors de la modification du mot de passe."
    );
}
