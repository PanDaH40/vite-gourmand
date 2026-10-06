<?php

require_once __DIR__ . "/db.php";

header("Content-Type: application/json; charset=utf-8");


try {

    /*
    |--------------------------------------------------------------------------
    | Récupération uniquement des avis acceptés
    |--------------------------------------------------------------------------
    */

    $sql = "
        SELECT
            avis.avis_id,
            avis.note,
            avis.description,
            utilisateur.prenom

        FROM avis

        INNER JOIN publie_utilisateur_avis
            ON avis.avis_id =
               publie_utilisateur_avis.avis_id

        INNER JOIN utilisateur
            ON publie_utilisateur_avis.utilisateur_id =
               utilisateur.utilisateur_id

        WHERE avis.statut = :statut

        ORDER BY avis.avis_id DESC
    ";


    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        "statut" => "Accepté"
    ]);


    $avis =
        $stmt->fetchAll(PDO::FETCH_ASSOC);


    echo json_encode([
        "success" => true,
        "avis" => $avis
    ], JSON_UNESCAPED_UNICODE);


} catch (PDOException $e) {

    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "error" => "Impossible de récupérer les avis."
    ], JSON_UNESCAPED_UNICODE);
}