<?php

session_start();

require_once __DIR__ . "/db.php";

header("Content-Type: application/json; charset=utf-8");


/*
|--------------------------------------------------------------------------
| Vérification de la connexion
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
| Vérification du rôle administrateur
|--------------------------------------------------------------------------
*/

$sqlRole = "
    SELECT role.libelle
    FROM possede_utilisateur_role

    INNER JOIN role
        ON possede_utilisateur_role.role_id = role.role_id

    WHERE possede_utilisateur_role.utilisateur_id = :utilisateur_id
    AND role.libelle = 'Administrateur'
    LIMIT 1
";

$stmtRole = $pdo->prepare($sqlRole);

$stmtRole->execute([
    "utilisateur_id" => $_SESSION["utilisateur_id"]
]);

$role = $stmtRole->fetch(PDO::FETCH_ASSOC);


if (!$role || $role["libelle"] !== "Administrateur") {

    http_response_code(403);

    echo json_encode([
        "success" => false,
        "error" => "Accès administrateur refusé."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/*
|--------------------------------------------------------------------------
| Récupération des commandes avec prêt de matériel
|--------------------------------------------------------------------------
*/

try {

    $sql = "
        SELECT
            commande.numero_commande,
            commande.date_prestation,
            commande.pret_materiel,
            commande.restitution_materiel,

            utilisateur.utilisateur_id,
            utilisateur.prenom,
            utilisateur.email,
            utilisateur.telephone

        FROM commande

        INNER JOIN commande_utilisateur
            ON commande.numero_commande =
               commande_utilisateur.numero_commande

        INNER JOIN utilisateur
            ON commande_utilisateur.utilisateur_id =
               utilisateur.utilisateur_id

        WHERE commande.pret_materiel = 1

        ORDER BY commande.date_prestation DESC
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute();

    $materiels = $stmt->fetchAll(PDO::FETCH_ASSOC);


    /*
    |--------------------------------------------------------------------------
    | Calcul des statistiques
    |--------------------------------------------------------------------------
    */

    $nombrePrets = count($materiels);

    $nombreAttente = 0;

    $nombreRestitues = 0;


    foreach ($materiels as $materiel) {

        if ((int) $materiel["restitution_materiel"] === 1) {

            $nombreRestitues++;

        } else {

            $nombreAttente++;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Réponse JSON
    |--------------------------------------------------------------------------
    */

    echo json_encode([
        "success" => true,

        "statistiques" => [
            "prets" => $nombrePrets,
            "attente" => $nombreAttente,
            "restitues" => $nombreRestitues
        ],

        "materiels" => $materiels

    ], JSON_UNESCAPED_UNICODE);


} catch (PDOException $e) {

    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "error" => "Erreur lors de la récupération du matériel."
    ], JSON_UNESCAPED_UNICODE);
}