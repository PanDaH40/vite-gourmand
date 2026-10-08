<?php

session_start();

require_once __DIR__ . "/db.php";
require_once __DIR__ . "/MongoDb.php";

header("Content-Type: application/json; charset=utf-8");

// Vérification de la connexion
if (!isset($_SESSION["utilisateur_id"])) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Utilisateur non connecté."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Vérification du rôle administrateur
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
        "message" => "Accès refusé."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Vérification de MongoDB
if ($mongo === null) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "MongoDB indisponible."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Récupération des filtres
$menuId = $_GET["menu_id"] ?? "";
$dateDebut = $_GET["date_debut"] ?? "";
$dateFin = $_GET["date_fin"] ?? "";

// Validation du menu
if ($menuId !== "" && (!ctype_digit((string) $menuId) || (int) $menuId <= 0)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Menu invalide."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Validation des dates
function dateValide($date) {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        return false;
    }

    $parties = explode("-", $date);

    return checkdate(
        (int) $parties[1],
        (int) $parties[2],
        (int) $parties[0]
    );
}

if (
    ($dateDebut !== "" && !dateValide($dateDebut)) ||
    ($dateFin !== "" && !dateValide($dateFin)) ||
    ($dateDebut !== "" && $dateFin !== "" && $dateDebut > $dateFin)
) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Période invalide."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {

    // Filtre initial sur les statuts
    $filtres = [
        "status" => [
            '$in' => [
                "acceptée",
                "terminée"
            ]
        ]
    ];

    // Filtre par menu
    if ($menuId !== "") {
        $filtres["menu.menuId"] = (int) $menuId;
    }

    // Filtre par date de commande
    if ($dateDebut !== "" || $dateFin !== "") {

        $filtres["orderedAt"] = [];

        if ($dateDebut !== "") {
            $filtres["orderedAt"]['$gte'] = $dateDebut;
        }

        if ($dateFin !== "") {
            $filtres["orderedAt"]['$lte'] = $dateFin;
        }
    }

    // Agrégation MongoDB
    $pipeline = [
        [
            '$match' => $filtres
        ],
        [
            '$group' => [
                '_id' => '$menu.menuId',

                'title' => [
                    '$first' => '$menu.title'
                ],

                'revenue' => [
                    '$sum' => '$order.total'
                ],

                'ordersCount' => [
                    '$sum' => 1
                ]
            ]
        ],
        [
            '$sort' => [
                'revenue' => -1
            ]
        ]
    ];

    $commande = new MongoDB\Driver\Command([
        "aggregate" => "orders_analytics",
        "pipeline" => $pipeline,
        "cursor" => new stdClass()
    ]);

    $cursor = $mongo->executeCommand(
        $mongoDatabase,
        $commande
    );

    $statistiques = [];

    foreach ($cursor as $document) {
        $statistiques[] = [
            "menu_id" => $document->_id,
            "titre" => $document->title,
            "chiffre_affaires" => $document->revenue,
            "nombre_commandes" => $document->ordersCount
        ];
    }

    echo json_encode([
        "success" => true,
        "source" => "MongoDB",
        "statistiques" => $statistiques
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {

    error_log(
        "Erreur statistiques MongoDB : " . $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Impossible de calculer les statistiques MongoDB."
    ], JSON_UNESCAPED_UNICODE);
}