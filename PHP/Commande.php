<?php
session_start();

require __DIR__ . "/db.php";

if (!isset($_SESSION["utilisateur_id"])) {
    header("Location: ../Connection.html");
    exit;
}

$html = file_get_contents("../Commande.html");

if ($html === false) {
    die("Erreur : impossible de charger Commande.html");
}

/* Corrige les chemins CSS / JS / images */
$html = str_replace(
    "<head>",
    '<head><base href="../">',
    $html
);

/* Récupère les menus depuis la base */
$sql = "SELECT menu_id, titre, nombre_personne_minimum, prix_par_personne
        FROM menu
        WHERE quantite_restante > 0";

$stmt = $pdo->query($sql);
$menus = $stmt->fetchAll(PDO::FETCH_ASSOC);

$options = "";

foreach ($menus as $menu) {
    $options .= '
    <option
        value="' . $menu["menu_id"] . '"
        data-min="' . $menu["nombre_personne_minimum"] . '"
        data-prix="' . $menu["prix_par_personne"] . '">
        ' . htmlspecialchars($menu["titre"]) . ' - ' . $menu["prix_par_personne"] . ' €/personne
    </option>';
}

/* Remplace le select écrit en dur dans Commande.html */
$html = preg_replace(
    '/<select id="menuSelect" name="menu_id">.*?<\/select>/s',
    '<select id="menuSelect" name="menu_id">' . $options . '</select>',
    $html,
    1
);

echo $html;
?>