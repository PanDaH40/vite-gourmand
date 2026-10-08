<?php

session_start();

require __DIR__ . "/db.php";

/* Vérifie si connecté */
if (!isset($_SESSION["utilisateur_id"])) {
    header("Location: ../Connection.html");
    exit;
}

/* Vérifie si administrateur ou employé */
$sqlRole = "
    SELECT role.libelle
    FROM possede_utilisateur_role

    INNER JOIN role
        ON possede_utilisateur_role.role_id = role.role_id

    WHERE possede_utilisateur_role.utilisateur_id = :utilisateur_id
    AND role.libelle IN ('Administrateur', 'Employé')

    LIMIT 1
";

$stmtRole = $pdo->prepare($sqlRole);

$stmtRole->execute([
    "utilisateur_id" => $_SESSION["utilisateur_id"]
]);

$role = $stmtRole->fetch(PDO::FETCH_ASSOC);

if (!$role) {
    die("Accès refusé.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $titre = trim($_POST["titre"]);
    $prix = (float) $_POST["prix"];
    $stock = (int) $_POST["stock"];
    $minimum = (int) $_POST["minimum"];
    $regime = trim($_POST["regime"]);
    $description = trim($_POST["description"]);

    if (
        empty($titre) ||
        $prix <= 0 ||
        $stock < 0 ||
        $minimum <= 0 ||
        empty($regime) ||
        empty($description)
    ) {
        die("Tous les champs sont obligatoires.");
    }

    /**
     * Vérification du jeton CSRF
     */
    $csrfToken = $_POST["csrf_token"] ?? "";

    if (
        !isset($_SESSION["csrf_token"]) ||
        !is_string($_SESSION["csrf_token"]) ||
        !is_string($csrfToken) ||
        !hash_equals($_SESSION["csrf_token"], $csrfToken)
    ) {
        http_response_code(403);
        die("Jeton de sécurité invalide.");
    }

    $sql = "
        INSERT INTO menu
        (
            titre,
            nombre_personne_minimum,
            prix_par_personne,
            regime,
            description,
            quantite_restante
        )
        VALUES
        (
            :titre,
            :minimum,
            :prix,
            :regime,
            :description,
            :stock
        )
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        "titre" => $titre,
        "minimum" => $minimum,
        "prix" => $prix,
        "regime" => $regime,
        "description" => $description,
        "stock" => $stock
    ]);

    echo "
    <script>
        alert('Menu ajouté avec succès.');
        window.location.href = '../AdminMenus.html';
    </script>
    ";

    exit;
}
?>