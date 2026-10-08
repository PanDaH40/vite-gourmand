<?php

require_once __DIR__ . "/db.php";

header("Content-Type: application/json; charset=utf-8");

// Vérification de l'identifiant du menu
$menuId = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$menuId || $menuId <= 0) {
    http_response_code(400);

    echo json_encode([
        "error" => "Identifiant de menu invalide."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

try {

    // Récupération des plats associés au menu
    $stmt = $pdo->prepare("
        SELECT
            plat.plat_id,
            plat.titre_plat
        FROM plat
        INNER JOIN propose_menu_plat
            ON propose_menu_plat.plat_id = plat.plat_id
        WHERE propose_menu_plat.menu_id = :menu_id
        ORDER BY plat.plat_id ASC
    ");

    $stmt->execute([
        "menu_id" => $menuId
    ]);

    $plats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(
        $plats,
        JSON_UNESCAPED_UNICODE
    );

} catch (PDOException $e) {

    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        "error" => "Impossible de récupérer les plats du menu."
    ], JSON_UNESCAPED_UNICODE);
}