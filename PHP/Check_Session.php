<?php

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

header("Content-Type: application/json; charset=utf-8");
header("Cache-Control: no-store");

require __DIR__ . "/db.php";

// Vérifier si une session existe
if (empty($_SESSION["utilisateur_id"])) {

    echo json_encode([
        "connecte" => false
    ]);

    exit;
}

try {

    $utilisateur_id = $_SESSION["utilisateur_id"];

    // Vérifier que l'utilisateur existe toujours
    // et déterminer s'il possède le rôle administrateur
    $sql = "
        SELECT
            u.prenom,
            EXISTS (
                SELECT 1
                FROM possede_utilisateur_role pur
                INNER JOIN role r
                    ON pur.role_id = r.role_id
                WHERE pur.utilisateur_id = u.utilisateur_id
                    AND r.libelle = 'Administrateur'
            ) AS est_admin
        FROM utilisateur u
        WHERE u.utilisateur_id = :utilisateur_id
        LIMIT 1
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        "utilisateur_id" => $utilisateur_id
    ]);

    $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$utilisateur) {

        $_SESSION = [];

        session_regenerate_id(true);

        echo json_encode([
            "connecte" => false
        ]);

        exit;
    }

    $estAdmin = (bool) $utilisateur["est_admin"];

    $reponse = [
        "connecte" => true,
        "prenom" => $utilisateur["prenom"],
        "admin" => $estAdmin
    ];

    // Générer un jeton CSRF pour tous les utilisateurs connectés
    if (empty($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }

    $reponse["csrf_token"] = $_SESSION["csrf_token"];
    

    echo json_encode(
        $reponse,
        JSON_UNESCAPED_UNICODE
    );

} catch (Throwable $e) {

    error_log("Erreur Check_Session : " . $e->getMessage());

    http_response_code(500);

    echo json_encode([
        "connecte" => false,
        "erreur" => "Impossible de vérifier la session."
    ]);
}
