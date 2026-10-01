<?php

require __DIR__ . "/db.php";

header("Content-Type: application/json; charset=utf-8");

if (isset($_GET["id"])) {

    $menuId = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

    if (!$menuId) {
        http_response_code(400);

        echo json_encode(
            ["error" => "Identifiant du menu invalide."],
            JSON_UNESCAPED_UNICODE
        );

        exit;
    }

    $sql = "SELECT
                menu_id,
                titre,
                nombre_personne_minimum,
                prix_par_personne,
                regime,
                description,
                quantite_restante
            FROM menu
            WHERE menu_id = :menu_id";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        "menu_id" => $menuId
    ]);

    $menu = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$menu) {
        http_response_code(404);

        echo json_encode(
            ["error" => "Menu introuvable."],
            JSON_UNESCAPED_UNICODE
        );

        exit;
    }

    echo json_encode($menu, JSON_UNESCAPED_UNICODE);
    exit;
}

$sql = "SELECT
            menu_id,
            titre,
            nombre_personne_minimum,
            prix_par_personne,
            regime,
            description,
            quantite_restante
        FROM menu
        ORDER BY menu_id DESC";

$stmt = $pdo->query($sql);

$menus = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($menus, JSON_UNESCAPED_UNICODE);