<?php

require_once __DIR__ . "/db.php";
require_once __DIR__ . "/Controllers/MenuController.php";

header("Content-Type: application/json; charset=utf-8");

try {

    $controller = new MenuController($pdo);


    /**
     * Récupération d'un seul menu
     * Exemple : Get_Menus.php?id=1
     */
    if (isset($_GET["id"])) {

        $menuId = (int) $_GET["id"];

        $menu = $controller->getMenuById($menuId);

        if (!$menu) {

            http_response_code(404);

            echo json_encode([
                "error" => "Menu introuvable."
            ], JSON_UNESCAPED_UNICODE);

            exit;
        }

        // On retourne directement le menu
        // pour conserver le fonctionnement existant.
        echo json_encode(
            $menu,
            JSON_UNESCAPED_UNICODE
        );

        exit;
    }


    /**
     * Récupération de tous les menus.
     */
    $menus = $controller->getAllMenus();

    // IMPORTANT :
    // On retourne directement le tableau
    // car les JS existants font menus.forEach().
    echo json_encode(
        $menus,
        JSON_UNESCAPED_UNICODE
    );


} catch (PDOException $e) {

    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        "error" => "Erreur lors de la récupération des menus."
    ], JSON_UNESCAPED_UNICODE);
}