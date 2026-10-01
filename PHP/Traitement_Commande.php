<?php

session_start();

require __DIR__ . "/db.php";

if (!isset($_SESSION["utilisateur_id"])) {
    header("Location: ../Connection.html");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../Menu.html");
    exit;
}

try {

    $utilisateur_id = (int) $_SESSION["utilisateur_id"];

    $menu_id = isset($_POST["menu_id"])
        ? (int) $_POST["menu_id"]
        : 0;

    $nombre_personne = isset($_POST["nombre_personne"])
        ? (int) $_POST["nombre_personne"]
        : 0;

    $date_prestation = $_POST["date_prestation"] ?? "";
    $heure_livraison = $_POST["heure_livraison"] ?? "";
    $ville = trim($_POST["ville"] ?? "");

    $pret_materiel =
        isset($_POST["pret_materiel"]) ? 1 : 0;


    /*
     * Vérification des données obligatoires
     */
    if (
        $menu_id <= 0 ||
        $nombre_personne <= 0 ||
        empty($date_prestation) ||
        empty($heure_livraison) ||
        empty($ville)
    ) {
        die("Veuillez remplir tous les champs obligatoires.");
    }


    /*
     * Récupération du menu directement depuis MySQL.
     *
     * Le prix et le minimum ne viennent donc pas
     * du navigateur.
     */
    $sqlMenu = "
        SELECT
            menu_id,
            prix_par_personne,
            nombre_personne_minimum
        FROM menu
        WHERE menu_id = :menu_id
    ";

    $stmtMenu = $pdo->prepare($sqlMenu);

    $stmtMenu->execute([
        "menu_id" => $menu_id
    ]);

    $menu = $stmtMenu->fetch(PDO::FETCH_ASSOC);

    if (!$menu) {
        die("Menu introuvable.");
    }


    /*
     * Vérification du nombre minimum de personnes.
     */
    $minimum =
        (int) $menu["nombre_personne_minimum"];

    if ($nombre_personne < $minimum) {

        die(
            "Ce menu nécessite au minimum " .
            $minimum .
            " personnes."
        );
    }


    /*
     * Calcul du prix depuis les données MySQL.
     */
    $prix_par_personne =
        (float) $menu["prix_par_personne"];

    $prix_menu =
        $prix_par_personne * $nombre_personne;


    /*
     * Réduction de 10 %
     * à partir de minimum + 5 personnes.
     */
    if ($nombre_personne >= $minimum + 5) {

        $reduction =
            $prix_menu * 0.10;

        $prix_menu =
            $prix_menu - $reduction;
    }


    /*
     * Livraison.
     *
     * Bordeaux : gratuite
     * Autre ville : 5 €
     */
    $prix_livraison = 0;

    if (strtolower($ville) !== "bordeaux") {
        $prix_livraison = 5;
    }


    /*
     * Création d'un numéro de commande.
     */
    $numero_commande =
        "CMD-" .
        date("YmdHis") .
        "-" .
        random_int(100, 999);


    /*
     * Transaction :
     * soit les trois INSERT fonctionnent,
     * soit aucun n'est enregistré.
     */
    $pdo->beginTransaction();


    /*
     * Enregistrement de la commande.
     */
    $sqlCommande = "
        INSERT INTO commande
        (
            numero_commande,
            date_commande,
            date_prestation,
            heure_livraison,
            prix_menu,
            nombre_personne,
            prix_livraison,
            statut,
            pret_materiel,
            restitution_materiel
        )
        VALUES
        (
            :numero_commande,
            CURDATE(),
            :date_prestation,
            :heure_livraison,
            :prix_menu,
            :nombre_personne,
            :prix_livraison,
            'en attente',
            :pret_materiel,
            0
        )
    ";

    $stmtCommande =
        $pdo->prepare($sqlCommande);

    $stmtCommande->execute([
        "numero_commande" => $numero_commande,
        "date_prestation" => $date_prestation,
        "heure_livraison" => $heure_livraison,
        "prix_menu" => $prix_menu,
        "nombre_personne" => $nombre_personne,
        "prix_livraison" => $prix_livraison,
        "pret_materiel" => $pret_materiel
    ]);


    /*
     * Association commande / utilisateur.
     */
    $sqlUtilisateur = "
        INSERT INTO commande_utilisateur
        (
            numero_commande,
            utilisateur_id
        )
        VALUES
        (
            :numero_commande,
            :utilisateur_id
        )
    ";

    $stmtUtilisateur =
        $pdo->prepare($sqlUtilisateur);

    $stmtUtilisateur->execute([
        "numero_commande" => $numero_commande,
        "utilisateur_id" => $utilisateur_id
    ]);


    /*
     * Association commande / menu.
     */
    $sqlMenuCommande = "
        INSERT INTO commande_menu
        (
            numero_commande,
            menu_id
        )
        VALUES
        (
            :numero_commande,
            :menu_id
        )
    ";

    $stmtMenuCommande =
        $pdo->prepare($sqlMenuCommande);

    $stmtMenuCommande->execute([
        "numero_commande" => $numero_commande,
        "menu_id" => $menu_id
    ]);


    /*
     * Tout s'est correctement passé.
     */
    $pdo->commit();


    echo "
        <script>
            alert('Commande enregistrée avec succès.');
            window.location.href = '../MesCommandes.html';
        </script>
    ";

    exit;


} catch (Throwable $e) {

    /*
     * Si une erreur survient pendant les INSERT,
     * on annule la transaction.
     */
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log($e->getMessage());

    echo "Une erreur est survenue lors de l'enregistrement de la commande.";
}
?>