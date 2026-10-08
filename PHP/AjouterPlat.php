<?php

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/Db.php';

if (!isset($_SESSION['utilisateur_id'])) {
    http_response_code(401);

    echo json_encode([
        'success' => false,
        'message' => 'Utilisateur non connecté.'
    ]);

    exit;
}

if (
    !isset($_POST['csrf_token']) ||
    !isset($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    http_response_code(403);

    echo json_encode([
        'success' => false,
        'message' => 'Jeton de sécurité invalide.'
    ]);

    exit;
}

$utilisateurId = (int) $_SESSION['utilisateur_id'];

try {

    // Vérification du rôle
    $stmtRole = $pdo->prepare("
        SELECT role.libelle
        FROM possede_utilisateur_role
        INNER JOIN role
            ON role.role_id = possede_utilisateur_role.role_id
        WHERE possede_utilisateur_role.utilisateur_id = :utilisateur_id
        AND role.libelle IN ('Administrateur', 'Employé')
        LIMIT 1
    ");

    $stmtRole->execute([
        ':utilisateur_id' => $utilisateurId
    ]);

    $role = $stmtRole->fetch(PDO::FETCH_ASSOC);

    if (!$role) {
        http_response_code(403);

        echo json_encode([
            'success' => false,
            'message' => 'Accès refusé.'
        ]);

        exit;
    }


    // Récupération des données
    $titrePlat = trim($_POST['titre_plat'] ?? '');
    $menuId = (int) ($_POST['menu_id'] ?? 0);


    // Vérification du titre
    if ($titrePlat === '') {
        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Le nom du plat est obligatoire.'
        ]);

        exit;
    }

    if (mb_strlen($titrePlat) > 50) {
        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Le nom du plat est trop long.'
        ]);

        exit;
    }


    // Vérification du menu
    if ($menuId <= 0) {
        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Veuillez choisir un menu.'
        ]);

        exit;
    }

    $stmtMenu = $pdo->prepare("
        SELECT menu_id
        FROM menu
        WHERE menu_id = :menu_id
        LIMIT 1
    ");

    $stmtMenu->execute([
        ':menu_id' => $menuId
    ]);

    $menu = $stmtMenu->fetch(PDO::FETCH_ASSOC);

    if (!$menu) {
        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Le menu sélectionné est invalide.'
        ]);

        exit;
    }


    // Début de la transaction
    $pdo->beginTransaction();


    // Ajout du plat
    $stmtPlat = $pdo->prepare("
        INSERT INTO plat (titre_plat)
        VALUES (:titre_plat)
    ");

    $stmtPlat->execute([
        ':titre_plat' => $titrePlat
    ]);

    $platId = (int) $pdo->lastInsertId();


    // Association du plat au menu
    $stmtAssociation = $pdo->prepare("
        INSERT INTO propose_menu_plat (menu_id, plat_id)
        VALUES (:menu_id, :plat_id)
    ");

    $stmtAssociation->execute([
        ':menu_id' => $menuId,
        ':plat_id' => $platId
    ]);


    // Validation
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Plat ajouté au menu avec succès.'
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de l\'ajout du plat.'
    ]);
}