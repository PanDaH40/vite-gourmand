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


    // Horaire reçu
    $horaireId = (int) ($_POST['horaire_id'] ?? 0);

    if ($horaireId <= 0) {
        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Horaire invalide.'
        ]);

        exit;
    }


    // Vérifier que l'horaire existe
    $stmtExiste = $pdo->prepare("
        SELECT horaire_id
        FROM horaire
        WHERE horaire_id = :horaire_id
        LIMIT 1
    ");

    $stmtExiste->execute([
        ':horaire_id' => $horaireId
    ]);

    if (!$stmtExiste->fetch(PDO::FETCH_ASSOC)) {
        http_response_code(404);

        echo json_encode([
            'success' => false,
            'message' => 'Horaire introuvable.'
        ]);

        exit;
    }


    // Suppression
    $stmt = $pdo->prepare("
        DELETE FROM horaire
        WHERE horaire_id = :horaire_id
    ");

    $stmt->execute([
        ':horaire_id' => $horaireId
    ]);


    echo json_encode([
        'success' => true,
        'message' => 'Horaire supprimé avec succès.'
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la suppression de l\'horaire.'
    ]);
}