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


    // Données reçues
    $platId = (int) ($_POST['plat_id'] ?? 0);
    $titrePlat = trim($_POST['titre_plat'] ?? '');
    $menuId = (int) ($_POST['menu_id'] ?? 0);


    if ($platId <= 0) {
        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Plat invalide.'
        ]);

        exit;
    }

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

    if ($menuId <= 0) {
        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Veuillez choisir un menu.'
        ]);

        exit;
    }


    // Vérifier que le plat existe
    $stmtPlatExiste = $pdo->prepare("
        SELECT plat_id
        FROM plat
        WHERE plat_id = :plat_id
        LIMIT 1
    ");

    $stmtPlatExiste->execute([
        ':plat_id' => $platId
    ]);

    if (!$stmtPlatExiste->fetch(PDO::FETCH_ASSOC)) {
        http_response_code(404);

        echo json_encode([
            'success' => false,
            'message' => 'Plat introuvable.'
        ]);

        exit;
    }


    // Vérifier que le menu existe
    $stmtMenu = $pdo->prepare("
        SELECT menu_id
        FROM menu
        WHERE menu_id = :menu_id
        LIMIT 1
    ");

    $stmtMenu->execute([
        ':menu_id' => $menuId
    ]);

    if (!$stmtMenu->fetch(PDO::FETCH_ASSOC)) {
        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Le menu sélectionné est invalide.'
        ]);

        exit;
    }


    // Modification du plat et de son association au menu
    $pdo->beginTransaction();


    // Modifier le nom du plat
    $stmtModifierPlat = $pdo->prepare("
        UPDATE plat
        SET titre_plat = :titre_plat
        WHERE plat_id = :plat_id
    ");

    $stmtModifierPlat->execute([
        ':titre_plat' => $titrePlat,
        ':plat_id' => $platId
    ]);


    // Supprimer l'ancienne association au menu
    $stmtSupprimerAssociation = $pdo->prepare("
        DELETE FROM propose_menu_plat
        WHERE plat_id = :plat_id
    ");

    $stmtSupprimerAssociation->execute([
        ':plat_id' => $platId
    ]);


    // Créer la nouvelle association
    $stmtAjouterAssociation = $pdo->prepare("
        INSERT INTO propose_menu_plat (menu_id, plat_id)
        VALUES (:menu_id, :plat_id)
    ");

    $stmtAjouterAssociation->execute([
        ':menu_id' => $menuId,
        ':plat_id' => $platId
    ]);


    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Plat modifié avec succès.'
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la modification du plat.'
    ]);
}