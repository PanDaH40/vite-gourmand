<?php

session_start();

require __DIR__ . "/db.php";

header("Content-Type: application/json; charset=utf-8");


/*
 * Vérification connexion
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
 * Vérification administrateur
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


/*
 * Lecture du JSON
 */
$donnees = json_decode(
    file_get_contents("php://input"),
    true
);

$menuId =
    isset($donnees["menu_id"])
        ? (int) $donnees["menu_id"]
        : 0;


if ($menuId <= 0) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Identifiant du menu invalide."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


try {

    /*
     * Vérifie que le menu existe
     */
    $stmtMenu = $pdo->prepare(
        "SELECT menu_id
         FROM menu
         WHERE menu_id = :menu_id"
    );

    $stmtMenu->execute([
        "menu_id" => $menuId
    ]);


    if (!$stmtMenu->fetch()) {

        http_response_code(404);

        echo json_encode([
            "success" => false,
            "message" => "Menu introuvable."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    /*
     * Vérifie si le menu est déjà utilisé
     * dans une commande.
     */
    $stmtCommande = $pdo->prepare(
        "SELECT COUNT(*)
         FROM commande_menu
         WHERE menu_id = :menu_id"
    );

    $stmtCommande->execute([
        "menu_id" => $menuId
    ]);

    $nombreCommandes =
        (int) $stmtCommande->fetchColumn();


    if ($nombreCommandes > 0) {

        http_response_code(409);

        echo json_encode([
            "success" => false,
            "message" =>
                "Ce menu est associé à une commande et ne peut pas être supprimé."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    /*
     * Suppression des éventuelles relations
     * régime et thème.
     */
    $pdo->beginTransaction();


    $stmtRegime = $pdo->prepare(
        "DELETE FROM adapte_menu_regime
         WHERE menu_id = :menu_id"
    );

    $stmtRegime->execute([
        "menu_id" => $menuId
    ]);


    $stmtTheme = $pdo->prepare(
        "DELETE FROM propose_menu_theme
         WHERE menu_id = :menu_id"
    );

    $stmtTheme->execute([
        "menu_id" => $menuId
    ]);


    $stmtPlat = $pdo->prepare(
        "DELETE FROM propose_menu_plat
         WHERE menu_id = :menu_id"
    );

    $stmtPlat->execute([
        "menu_id" => $menuId
    ]);


    /*
     * Suppression du menu
     */
    $stmtDelete = $pdo->prepare(
        "DELETE FROM menu
         WHERE menu_id = :menu_id"
    );

    $stmtDelete->execute([
        "menu_id" => $menuId
    ]);


    $pdo->commit();


    echo json_encode([
        "success" => true,
        "message" => "Menu supprimé avec succès."
    ], JSON_UNESCAPED_UNICODE);


} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" =>
            "Une erreur est survenue lors de la suppression."
    ], JSON_UNESCAPED_UNICODE);
}