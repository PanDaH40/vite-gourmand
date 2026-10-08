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


    // Récupération des horaires
    $stmt = $pdo->query("
        SELECT
            horaire_id,
            jour,
            heure_ouverture,
            heure_fermeture
        FROM horaire
        ORDER BY horaire_id ASC
    ");

    $horaires = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(
        $horaires,
        JSON_UNESCAPED_UNICODE
    );

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors du chargement des horaires.'
    ]);
}