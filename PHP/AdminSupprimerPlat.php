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


    // Vérification du plat
    $platId = (int) ($_POST['plat_id'] ?? 0);

    if ($platId <= 0) {
        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Plat invalide.'
        ]);

        exit;
    }


    // Transaction pour supprimer proprement les relations
    $pdo->beginTransaction();


    // Suppression des relations avec les menus
    $stmtMenu = $pdo->prepare("
        DELETE FROM propose_menu_plat
        WHERE plat_id = :plat_id
    ");

    $stmtMenu->execute([
        ':plat_id' => $platId
    ]);


    // Suppression des relations avec les allergènes
    $stmtAllergene = $pdo->prepare("
        DELETE FROM contient_plat_allergene
        WHERE plat_id = :plat_id
    ");

    $stmtAllergene->execute([
        ':plat_id' => $platId
    ]);


    // Suppression du plat
    $stmtPlat = $pdo->prepare("
        DELETE FROM plat
        WHERE plat_id = :plat_id
    ");

    $stmtPlat->execute([
        ':plat_id' => $platId
    ]);


    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Plat supprimé avec succès.'
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la suppression du plat.'
    ]);
}