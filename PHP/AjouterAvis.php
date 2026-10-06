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
| POST uniquement
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    http_response_code(405);

    echo json_encode([
        "success" => false,
        "error" => "Méthode non autorisée."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/*
|--------------------------------------------------------------------------
| Lecture des données
|--------------------------------------------------------------------------
*/

$data = json_decode(
    file_get_contents("php://input"),
    true
);

if (!is_array($data)) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "error" => "Données invalides."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


$numeroCommande =
    trim($data["numero_commande"] ?? "");

$note =
    (int) ($data["note"] ?? 0);

$description =
    trim($data["description"] ?? "");


/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

if ($numeroCommande === "") {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "error" => "Numéro de commande manquant."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


if ($note < 1 || $note > 5) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "error" => "La note doit être comprise entre 1 et 5."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


if (
    $description === "" ||
    mb_strlen($description) > 50
) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "error" => "Le commentaire doit contenir entre 1 et 50 caractères."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


try {

    /*
    |--------------------------------------------------------------------------
    | Vérifier que la commande appartient bien à l'utilisateur
    |--------------------------------------------------------------------------
    */

    $sqlCommande = "
        SELECT
            commande.numero_commande,
            commande.statut

        FROM commande

        INNER JOIN commande_utilisateur
            ON commande.numero_commande =
               commande_utilisateur.numero_commande

        WHERE commande.numero_commande = :numero_commande
          AND commande_utilisateur.utilisateur_id = :utilisateur_id

        LIMIT 1
    ";


    $stmtCommande =
        $pdo->prepare($sqlCommande);


    $stmtCommande->execute([

        "numero_commande" =>
            $numeroCommande,

        "utilisateur_id" =>
            $_SESSION["utilisateur_id"]
    ]);


    $commande =
        $stmtCommande->fetch(PDO::FETCH_ASSOC);


    if (!$commande) {

        http_response_code(403);

        echo json_encode([
            "success" => false,
            "error" => "Cette commande ne vous appartient pas."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | La commande doit être terminée
    |--------------------------------------------------------------------------
    */

    $statut = mb_strtolower(
        trim($commande["statut"])
    );


    $statutsTermines = [
        "terminée",
        "terminee",
        "terminé",
        "termine"
    ];


    if (!in_array(
        $statut,
        $statutsTermines,
        true
    )) {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "error" => "Vous pouvez laisser un avis uniquement après une commande terminée."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Transaction
    |--------------------------------------------------------------------------
    */

    $pdo->beginTransaction();


    /*
    |--------------------------------------------------------------------------
    | Génération de l'identifiant
    |--------------------------------------------------------------------------
    |
    | avis_id n'est pas AUTO_INCREMENT dans le schéma actuel.
    |
    */

    $stmtId = $pdo->query("
        SELECT COALESCE(MAX(avis_id), 0) + 1
        AS prochain_id
        FROM avis
    ");


    $prochainId =
        (int) $stmtId->fetchColumn();


    /*
    |--------------------------------------------------------------------------
    | Création de l'avis
    |--------------------------------------------------------------------------
    */

    $stmtAvis = $pdo->prepare("
        INSERT INTO avis
        (
            avis_id,
            note,
            description,
            statut
        )

        VALUES
        (
            :avis_id,
            :note,
            :description,
            :statut
        )
    ");


    $stmtAvis->execute([

        "avis_id" =>
            $prochainId,

        "note" =>
            (string) $note,

        "description" =>
            $description,

        "statut" =>
            "En attente"
    ]);


    /*
    |--------------------------------------------------------------------------
    | Liaison avis / utilisateur
    |--------------------------------------------------------------------------
    */

    $stmtRelation = $pdo->prepare("
        INSERT INTO publie_utilisateur_avis
        (
            avis_id,
            utilisateur_id
        )

        VALUES
        (
            :avis_id,
            :utilisateur_id
        )
    ");


    $stmtRelation->execute([

        "avis_id" =>
            $prochainId,

        "utilisateur_id" =>
            $_SESSION["utilisateur_id"]
    ]);


    $pdo->commit();


    echo json_encode([
        "success" => true,
        "message" => "Votre avis a été envoyé et sera vérifié avant publication."
    ], JSON_UNESCAPED_UNICODE);


} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "error" => "Erreur lors de l'enregistrement de l'avis."
    ], JSON_UNESCAPED_UNICODE);
}