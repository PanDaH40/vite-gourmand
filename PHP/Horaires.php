<?php

require_once __DIR__ . "/db.php";

header("Content-Type: application/json; charset=utf-8");

try {

    $sql = "
        SELECT
            horaire_id,
            jour,
            heure_ouverture,
            heure_fermeture

        FROM horaire

        ORDER BY horaire_id ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $horaires = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "horaires" => $horaires
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {

    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "error" => "Impossible de récupérer les horaires."
    ], JSON_UNESCAPED_UNICODE);
}