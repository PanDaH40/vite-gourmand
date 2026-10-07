<?php

session_start();

require __DIR__ . "/db.php";

header("Content-Type: application/json; charset=utf-8");


/*
 * Vérification de la connexion
 */
if (!isset($_SESSION["utilisateur_id"])) {

    http_response_code(401);

    echo json_encode([
        "success" => false,
        "message" => "Utilisateur non connecté."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/*
 * Vérification du rôle administrateur
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


if (
    !$role ||
    $role["libelle"] !== "Administrateur"
) {

    http_response_code(403);

    echo json_encode([
        "success" => false,
        "message" => "Accès refusé."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/*
 * POST uniquement
 */
if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    http_response_code(405);

    echo json_encode([
        "success" => false,
        "message" => "Méthode non autorisée."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

/**
 * Vérification du jeton CSRF
 */
$csrfToken = $_SERVER["HTTP_X_CSRF_TOKEN"] ?? "";

if (
    !isset($_SESSION["csrf_token"]) ||
    !is_string($_SESSION["csrf_token"]) ||
    !is_string($csrfToken) ||
    !hash_equals($_SESSION["csrf_token"], $csrfToken)
) {

    http_response_code(403);

    echo json_encode([
        "success" => false,
        "message" => "Jeton de sécurité invalide."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

/*
 * Récupération des données JSON
 */
$donnees = json_decode(
    file_get_contents("php://input"),
    true
);


$menuId =
    isset($donnees["menu_id"])
        ? (int) $donnees["menu_id"]
        : 0;

$titre =
    trim($donnees["titre"] ?? "");

$prix =
    isset($donnees["prix"])
        ? (float) $donnees["prix"]
        : 0;

$stock =
    isset($donnees["stock"])
        ? (int) $donnees["stock"]
        : -1;

$minimum =
    isset($donnees["minimum"])
        ? (int) $donnees["minimum"]
        : 0;

$regime =
    trim($donnees["regime"] ?? "");

$description =
    trim($donnees["description"] ?? "");


/*
 * Validation
 */
if (
    $menuId <= 0 ||
    $titre === "" ||
    $prix <= 0 ||
    $stock < 0 ||
    $minimum <= 0 ||
    $description === ""
) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Les informations du menu sont invalides."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


$regimesAutorises = [
    "Classique",
    "Végétarien",
    "Vegan"
];


if (!in_array(
    $regime,
    $regimesAutorises,
    true
)) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Régime invalide."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


try {

    /*
     * Modification du menu
     */
    $sql = "
        UPDATE menu

        SET
            titre = :titre,
            nombre_personne_minimum = :minimum,
            prix_par_personne = :prix,
            regime = :regime,
            description = :description,
            quantite_restante = :stock

        WHERE menu_id = :menu_id
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        "titre" => $titre,
        "minimum" => $minimum,
        "prix" => $prix,
        "regime" => $regime,
        "description" => $description,
        "stock" => $stock,
        "menu_id" => $menuId
    ]);


    echo json_encode([
        "success" => true,
        "message" => "Menu modifié avec succès."
    ], JSON_UNESCAPED_UNICODE);


} catch (PDOException $e) {

    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Erreur lors de la modification du menu."
    ], JSON_UNESCAPED_UNICODE);
}