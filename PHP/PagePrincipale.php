<?php
/* session_start();

$html = file_get_contents("../PagePrincipale.html");

if ($html === false) {
    die("Erreur : impossible de charger PagePrincipale.html");
}

/* Corrige les chemins */
$html = str_replace(
    "<head>",
    '<head><base href="../">',
    $html
);

/* Si utilisateur connecté */
if (isset($_SESSION["utilisateur_id"])) {

    /* Supprime le lien connexion */
    $html = preg_replace(
        '/<a href="Connection\.html">Connexion<\/a>/',
        '',
        $html,
        1
    );

    /* Bloc utilisateur */
    $blocUtilisateur = '
    <div class="user-menu">
        <span>Bonjour '
        . htmlspecialchars($_SESSION["prenom"]) .
        '</span>

        <a href="php/Deconnexion.php">
            Déconnexion
        </a>
    </div>';

    /* Ajoute le bloc après </nav> */
    $html = preg_replace(
        '/<\/nav>/',
        '</nav>' . $blocUtilisateur,
        $html,
        1
    );
}

echo $html;
?>