<?php
/* session_start();

$html = file_get_contents("../Menu.html");

if ($html === false) {
    die("Erreur : impossible de charger Menu.html");
}

$html = str_replace(
    "<head>",
    '<head><base href="../">',
    $html
);

$html = preg_replace(
    '/<div class="user-menu">.*?<\/div>/s',
    '',
    $html,
    1
);

if (isset($_SESSION["utilisateur_id"])) {

    $html = preg_replace(
        '/<a href="Connection\.html">Connexion<\/a>/',
        '',
        $html,
        1
    );

    $blocUtilisateur = '
    <div class="user-menu">
        <span>Bonjour ' . htmlspecialchars($_SESSION["prenom"]) . '</span>
        <a href="php/Deconnexion.php">Déconnexion</a>
    </div>';

    $html = preg_replace(
        '/<\/nav>/',
        '</nav>' . $blocUtilisateur,
        $html,
        1
    );
}

echo $html;
?>