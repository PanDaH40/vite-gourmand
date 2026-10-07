<?php
session_start();

require __DIR__ . "/db.php";

header("Content-Type: application/json; charset=utf-8");

/**
 * Vérification de la connexion.
 */
if (!isset($_SESSION["utilisateur_id"])) {
    http_response_code(401);

    echo json_encode([
        "success" => false,
        "message" => "Utilisateur non connecté."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

/**
 * Vérification de la méthode POST.
 */
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);

    echo json_encode([
        "success" => false,
        "message" => "Méthode non autorisée."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

/**
 * Protection CSRF.
 */
$csrfToken = $_SERVER["HTTP_X_CSRF_TOKEN"] ?? "";

if (
    !isset($_SESSION["csrf_token"]) ||
    !is_string($_SESSION["csrf_token"]) ||
    !hash_equals($_SESSION["csrf_token"], $csrfToken)
) {
    http_response_code(403);

    echo json_encode([
        "success" => false,
        "message" => "Jeton de sécurité invalide."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

/**
 * Récupération des données.
 */
$donnees = json_decode(
    file_get_contents("php://input"),
    true
);

$numeroCommande = is_array($donnees)
    ? ($donnees["numero_commande"] ?? "")
    : "";

if (!is_string($numeroCommande) ||
    trim($numeroCommande) === "") {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Numéro de commande manquant."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

$numeroCommande = trim($numeroCommande);
$utilisateurId = (int) $_SESSION["utilisateur_id"];

try {

    /**
     * Début de la transaction.
     */
    $pdo->beginTransaction();

    /**
     * Vérification de la commande,
     * de son propriétaire et de son statut.
     */
    $sqlCommande = "
        SELECT
            c.numero_commande,
            c.nombre_personne,
            c.statut
        FROM commande c
        INNER JOIN commande_utilisateur cu
            ON c.numero_commande = cu.numero_commande
        WHERE c.numero_commande = :numero_commande
        AND cu.utilisateur_id = :utilisateur_id
        LIMIT 1
        FOR UPDATE
    ";

    $stmtCommande = $pdo->prepare($sqlCommande);

    $stmtCommande->execute([
        "numero_commande" => $numeroCommande,
        "utilisateur_id" => $utilisateurId
    ]);

    $commande = $stmtCommande->fetch(PDO::FETCH_ASSOC);

    if (!$commande) {
        $pdo->rollBack();

        http_response_code(404);

        echo json_encode([
            "success" => false,
            "message" => "Commande introuvable."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    /**
     * Annulation autorisée uniquement
     * pour les commandes en attente.
     */
    if ($commande["statut"] !== "en attente") {
        $pdo->rollBack();

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "Cette commande ne peut plus être annulée."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    $nombrePersonnes =
        (int) $commande["nombre_personne"];

    /**
     * Restitution du stock.
     */
    $sqlStock = "
        UPDATE menu
        INNER JOIN commande_menu
            ON menu.menu_id = commande_menu.menu_id
        SET menu.quantite_restante =
            menu.quantite_restante + :quantite
        WHERE commande_menu.numero_commande = :numero_commande
    ";

    $stmtStock = $pdo->prepare($sqlStock);

    $stmtStock->execute([
        "quantite" => $nombrePersonnes,
        "numero_commande" => $numeroCommande
    ]);

    if ($stmtStock->rowCount() !== 1) {
        throw new RuntimeException(
            "Impossible de restituer le stock."
        );
    }

    /**
     * Modification du statut.
     */
    $sqlUpdate = "
        UPDATE commande
        SET statut = 'annulée'
        WHERE numero_commande = :numero_commande
        AND statut = 'en attente'
    ";

    $stmtUpdate = $pdo->prepare($sqlUpdate);

    $stmtUpdate->execute([
        "numero_commande" => $numeroCommande
    ]);

    if ($stmtUpdate->rowCount() !== 1) {
        throw new RuntimeException(
            "Impossible d'annuler la commande."
        );
    }

    /**
     * Validation des modifications.
     */
    $pdo->commit();

    echo json_encode([
        "success" => true,
        "message" => "Commande annulée avec succès."
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Erreur lors de l'annulation."
    ], JSON_UNESCAPED_UNICODE);
}
