<?php

session_start();

require_once __DIR__ . "/db.php";

header("Content-Type: application/json; charset=utf-8");


/*
|--------------------------------------------------------------------------
| Utilisateur connecté
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["utilisateur_id"])) {

    http_response_code(401);

    echo json_encode([
        "success" => false,
        "error" => "Utilisateur non connecté."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/*
|--------------------------------------------------------------------------
| Vérification administrateur
|--------------------------------------------------------------------------
*/

$sqlRole = "
    SELECT role.libelle

    FROM possede_utilisateur_role

    INNER JOIN role
        ON possede_utilisateur_role.role_id =
           role.role_id

   WHERE possede_utilisateur_role.utilisateur_id = :utilisateur_id
    AND role.libelle IN ('Administrateur', 'Employé')
    LIMIT 1
";


$stmtRole = $pdo->prepare($sqlRole);

$stmtRole->execute([
    "utilisateur_id" =>
        $_SESSION["utilisateur_id"]
]);


$role =
    $stmtRole->fetch(PDO::FETCH_ASSOC);


if (!$role) {

    http_response_code(403);

    echo json_encode([
        "success" => false,
        "error" => "Accès refusé."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/*
|--------------------------------------------------------------------------
| Récupération des avis
|--------------------------------------------------------------------------
*/

try {

    $sql = "
        SELECT
            avis.avis_id,
            avis.note,
            avis.description,
            avis.statut,

            utilisateur.utilisateur_id,
            utilisateur.prenom,
            utilisateur.email

        FROM avis

        INNER JOIN publie_utilisateur_avis
            ON avis.avis_id =
               publie_utilisateur_avis.avis_id

        INNER JOIN utilisateur
            ON publie_utilisateur_avis.utilisateur_id =
               utilisateur.utilisateur_id

        ORDER BY avis.avis_id DESC
    ";


    $stmt = $pdo->prepare($sql);

    $stmt->execute();


    $avis =
        $stmt->fetchAll(PDO::FETCH_ASSOC);


    /*
    |--------------------------------------------------------------------------
    | Statistiques
    |--------------------------------------------------------------------------
    */

    $attente = 0;
    $acceptes = 0;
    $refuses = 0;


    foreach ($avis as $unAvis) {

        $statut = mb_strtolower(
            trim($unAvis["statut"] ?? "")
        );


        if ($statut === "en attente") {

            $attente++;

        } elseif (
            $statut === "accepté" ||
            $statut === "accepte" ||
            $statut === "acceptée" ||
            $statut === "acceptee"
        ) {

            $acceptes++;

        } elseif (
            $statut === "refusé" ||
            $statut === "refuse" ||
            $statut === "refusée" ||
            $statut === "refusee"
        ) {

            $refuses++;
        }
    }


    echo json_encode([

        "success" => true,

        "statistiques" => [
            "attente" => $attente,
            "acceptes" => $acceptes,
            "refuses" => $refuses
        ],

        "avis" => $avis

    ], JSON_UNESCAPED_UNICODE);


} catch (PDOException $e) {

    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "error" => "Erreur lors de la récupération des avis."
    ], JSON_UNESCAPED_UNICODE);
}