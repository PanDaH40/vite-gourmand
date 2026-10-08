<?php

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/db.php';

if (!isset($_SESSION['utilisateur_id'])) {
    http_response_code(401);

    echo json_encode([
        'success' => false,
        'message' => 'Utilisateur non connecté.'
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


    // Récupération des plats avec leur menu
    $stmt = $pdo->query("
        SELECT
            plat.plat_id,
            plat.titre_plat,
            menu.menu_id,
            menu.titre AS menu_titre
        FROM plat
        LEFT JOIN propose_menu_plat
            ON propose_menu_plat.plat_id = plat.plat_id
        LEFT JOIN menu
            ON menu.menu_id = propose_menu_plat.menu_id
        ORDER BY plat.plat_id DESC
    ");

    $plats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(
        $plats,
        JSON_UNESCAPED_UNICODE
    );

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors du chargement des plats.'
    ]);
}