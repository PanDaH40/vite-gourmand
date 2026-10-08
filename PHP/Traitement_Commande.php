<?php

session_start();

require __DIR__ . "/db.php";
require_once __DIR__ . "/Mailer.php";


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

    // Récupération de l'adresse de livraison
    $adresse_livraison = trim(
        $_POST["adresse_livraison"] ?? ""
    );

    $pret_materiel =
        isset($_POST["pret_materiel"]) ? 1 : 0;


    /**
     * Vérification des données obligatoires.
     */
    if (
        $menu_id <= 0 ||
        $nombre_personne <= 0 ||
        empty($date_prestation) ||
        empty($heure_livraison) ||
        empty($ville) ||
        $adresse_livraison === ""
    ) {
        die("Veuillez remplir tous les champs obligatoires.");
    }


    /**
     * Récupération du client connecté.
     *
     * On récupère son prénom et son adresse e-mail
     * pour pouvoir envoyer la confirmation.
     */
    $sqlClient = "
        SELECT
            prenom,
            email
        FROM utilisateur
        WHERE utilisateur_id = :utilisateur_id
        LIMIT 1
    ";

    $stmtClient = $pdo->prepare($sqlClient);

    $stmtClient->execute([
        "utilisateur_id" => $utilisateur_id
    ]);

    $client = $stmtClient->fetch(PDO::FETCH_ASSOC);

    if (!$client) {
        die("Utilisateur introuvable.");
    }


    /**
     * Récupération du menu directement depuis MySQL.
     *
     * Le prix, le minimum et le stock ne viennent
     * donc pas du navigateur.
     */
    $sqlMenu = "
        SELECT
            menu_id,
            titre,
            prix_par_personne,
            nombre_personne_minimum,
            quantite_restante
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


    /**
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


    /**
     * Calcul du prix depuis les données MySQL.
     */
    $prix_par_personne =
        (float) $menu["prix_par_personne"];

    $prix_menu =
        $prix_par_personne * $nombre_personne;


    /**
     * Réduction de 10 %
     * à partir de minimum + 5 personnes.
     */
    if ($nombre_personne >= $minimum + 5) {

        $reduction =
            $prix_menu * 0.10;

        $prix_menu =
            $prix_menu - $reduction;
    }


    /**
     * Livraison.
     *
     * Bordeaux : gratuite
     * Hors Bordeaux : 5 € + 0,59 € par kilomètre.
     */
    $prix_livraison = 0;

    if (mb_strtolower($ville, "UTF-8") !== "bordeaux") {

        /**
         * Création de l'adresse complète.
         */
        $adresse_complete =
            $adresse_livraison . ", " .
            $ville . ", France";


        /**
         * 1 - Recherche des coordonnées
         * de l'adresse de livraison.
         */
        $url_geocodage =
            "https://nominatim.openstreetmap.org/search" .
            "?format=json" .
            "&limit=1" .
            "&countrycodes=fr" .
            "&q=" .
            urlencode($adresse_complete);


        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url_geocodage,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_USERAGENT => "ViteEtGourmand/1.0"
        ]);

        $reponse_geocodage = curl_exec($curl);

        if ($reponse_geocodage === false) {

            curl_close($curl);

            die(
                "Impossible de rechercher " .
                "l'adresse de livraison."
            );
        }


        $code_http =
            curl_getinfo(
                $curl,
                CURLINFO_HTTP_CODE
            );

        curl_close($curl);


        if ($code_http !== 200) {

            die(
                "Impossible de rechercher " .
                "l'adresse de livraison."
            );
        }


        $resultat_geocodage =
            json_decode(
                $reponse_geocodage,
                true
            );


        if (
            !is_array($resultat_geocodage) ||
            empty($resultat_geocodage)
        ) {

            die(
                "Adresse de livraison introuvable."
            );
        }


        $latitude_destination =
            (float) $resultat_geocodage[0]["lat"];

        $longitude_destination =
            (float) $resultat_geocodage[0]["lon"];


        /**
         * 2 - Coordonnées du centre de Bordeaux.
         */
        $latitude_bordeaux = 44.8378;
        $longitude_bordeaux = -0.5792;


        /**
         * 3 - Calcul de la distance routière.
         *
         * OSRM attend les coordonnées sous la forme :
         * longitude,latitude
         */
        $url_route =
            "https://router.project-osrm.org/route/v1/driving/" .
            $longitude_bordeaux . "," .
            $latitude_bordeaux . ";" .
            $longitude_destination . "," .
            $latitude_destination .
            "?overview=false";


        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url_route,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_USERAGENT => "ViteEtGourmand/1.0"
        ]);

        $reponse_route =
            curl_exec($curl);


        if ($reponse_route === false) {

            curl_close($curl);

            die(
                "Impossible de calculer " .
                "la distance de livraison."
            );
        }


        $code_http =
            curl_getinfo(
                $curl,
                CURLINFO_HTTP_CODE
            );

        curl_close($curl);


        if ($code_http !== 200) {

            die(
                "Impossible de calculer " .
                "la distance de livraison."
            );
        }


        $resultat_route =
            json_decode(
                $reponse_route,
                true
            );


        if (
            !isset($resultat_route["code"]) ||
            $resultat_route["code"] !== "Ok" ||
            !isset($resultat_route["routes"][0]["distance"])
        ) {

            die(
                "Aucun itinéraire de livraison trouvé."
            );
        }


        /**
         * 4 - OSRM retourne la distance en mètres.
         * Conversion en kilomètres.
         */
        $distance_km =
            (float) $resultat_route["routes"][0]["distance"]
            / 1000;


        /**
         * 5 - Calcul des frais de livraison.
         *
         * 5 € fixes
         * +
         * 0,59 € par kilomètre.
         */
        $prix_livraison =
            5 + ($distance_km * 0.59);


        /**
         * Arrondi à deux chiffres après la virgule.
         */
        $prix_livraison =
            round($prix_livraison, 2);
    }


    /**
     * Création d'un numéro de commande.
     */
    $numero_commande =
        "CMD-" .
        date("YmdHis") .
        "-" .
        random_int(100, 999);


    /**
     * Transaction :
     *
     * Le stock et les trois INSERT doivent
     * tous réussir.
     *
     * En cas d'erreur, aucune modification
     * n'est conservée.
     */
    $pdo->beginTransaction();


    /**
     * Vérification et déduction du stock.
     */
    $sqlStock = "
        UPDATE menu
        SET quantite_restante =
            quantite_restante - :quantite
        WHERE menu_id = :menu_id
        AND quantite_restante >= :quantite_minimum
    ";

    $stmtStock = $pdo->prepare($sqlStock);

    $stmtStock->execute([
        "quantite" => $nombre_personne,
        "menu_id" => $menu_id,
        "quantite_minimum" => $nombre_personne
    ]);


    if ($stmtStock->rowCount() !== 1) {

        $pdo->rollBack();

        die(
            "Stock insuffisant pour ce menu. " .
            "Veuillez réduire le nombre de personnes."
        );
    }


    /**
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


    /**
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


    /**
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


    /**
     * La commande est enregistrée
     * et le stock est mis à jour.
     */
    $pdo->commit();


    /**
     * E-mail de confirmation de commande.
     *
     * L'envoi est effectué après le commit :
     * une erreur SMTP ne doit pas annuler
     * une commande déjà enregistrée.
     */
    $prenomSecurise =
        htmlspecialchars(
            $client["prenom"],
            ENT_QUOTES,
            "UTF-8"
        );

    $menuSecurise =
        htmlspecialchars(
            $menu["titre"],
            ENT_QUOTES,
            "UTF-8"
        );

    $numeroSecurise =
        htmlspecialchars(
            $numero_commande,
            ENT_QUOTES,
            "UTF-8"
        );

    $adresseSecurisee =
        htmlspecialchars(
            $adresse_livraison,
            ENT_QUOTES,
            "UTF-8"
        );

    $villeSecurisee =
        htmlspecialchars(
            $ville,
            ENT_QUOTES,
            "UTF-8"
        );


    $prixMenuAffiche =
        number_format(
            $prix_menu,
            2,
            ",",
            " "
        );

    $prixLivraisonAffiche =
        number_format(
            $prix_livraison,
            2,
            ",",
            " "
        );

    $prixTotal =
        $prix_menu + $prix_livraison;

    $prixTotalAffiche =
        number_format(
            $prixTotal,
            2,
            ",",
            " "
        );


    $contenuHtml = "
        <h1>Confirmation de votre commande</h1>

        <p>
            Bonjour {$prenomSecurise},
        </p>

        <p>
            Votre commande Vite & Gourmand
            a bien été enregistrée.
        </p>

        <h2>Commande {$numeroSecurise}</h2>

        <p>
            <strong>Menu :</strong>
            {$menuSecurise}
        </p>

        <p>
            <strong>Nombre de personnes :</strong>
            {$nombre_personne}
        </p>

        <p>
            <strong>Date de prestation :</strong>
            {$date_prestation}
        </p>

        <p>
            <strong>Heure de livraison :</strong>
            {$heure_livraison}
        </p>

        <p>
            <strong>Adresse de livraison :</strong><br>
            {$adresseSecurisee}<br>
            {$villeSecurisee}
        </p>

        <p>
            <strong>Prix du menu :</strong>
            {$prixMenuAffiche} €
        </p>

        <p>
            <strong>Frais de livraison :</strong>
            {$prixLivraisonAffiche} €
        </p>

        <p>
            <strong>Total :</strong>
            {$prixTotalAffiche} €
        </p>

        <p>
            Statut actuel :
            <strong>En attente</strong>
        </p>

        <p>
            Merci pour votre commande.
        </p>

        <p>
            Vite & Gourmand
        </p>
    ";


    $contenuTexte =
        "Bonjour " . $client["prenom"] . ",\n\n" .
        "Votre commande Vite & Gourmand a bien été enregistrée.\n\n" .
        "Numéro : " . $numero_commande . "\n" .
        "Menu : " . $menu["titre"] . "\n" .
        "Nombre de personnes : " . $nombre_personne . "\n" .
        "Date de prestation : " . $date_prestation . "\n" .
        "Heure de livraison : " . $heure_livraison . "\n" .
        "Adresse : " . $adresse_livraison . ", " . $ville . "\n" .
        "Prix du menu : " . $prixMenuAffiche . " €\n" .
        "Frais de livraison : " . $prixLivraisonAffiche . " €\n" .
        "Total : " . $prixTotalAffiche . " €\n" .
        "Statut : En attente\n\n" .
        "Merci pour votre commande.\n" .
        "Vite & Gourmand";


    envoyerEmail(
        $client["email"],
        "Confirmation de votre commande " . $numero_commande,
        $contenuHtml,
        $contenuTexte
    );


    echo "
        <script>
            alert('Commande enregistrée avec succès.');
            window.location.href = '../MesCommandes.html';
        </script>
    ";

    exit;


} catch (Throwable $e) {

    /**
     * En cas d'erreur pendant la transaction,
     * on annule toutes les modifications.
     */
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log($e->getMessage());

    echo "Une erreur est survenue lors de l'enregistrement de la commande.";
}