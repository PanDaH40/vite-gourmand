<?php
session_start();

require __DIR__ . "/db.php";

if (!isset($_SESSION["utilisateur_id"])) {
    header("Location: ../Connection.html");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    try {
        $utilisateur_id = $_SESSION["utilisateur_id"];

        $menu_id = (int) $_POST["menu_id"];
        $nombre_personne = (int) $_POST["nombre_personne"];
        $date_prestation = $_POST["date_prestation"];
        $heure_livraison = $_POST["heure_livraison"];
        $ville = trim($_POST["ville"]);

        $pret_materiel = isset($_POST["pret_materiel"]) ? 1 : 0;

        $numero_commande = "CMD-" . date("YmdHis");

        $sqlMenu = "SELECT prix_par_personne
                    FROM menu
                    WHERE menu_id = :menu_id";

        $stmtMenu = $pdo->prepare($sqlMenu);
        $stmtMenu->execute([
            "menu_id" => $menu_id
        ]);

        $menu = $stmtMenu->fetch(PDO::FETCH_ASSOC);

        if (!$menu) {
            die("Menu introuvable.");
        }

        $prix_menu = $menu["prix_par_personne"] * $nombre_personne;

        $prix_livraison = 0;

        if (strtolower($ville) !== "bordeaux") {
            $prix_livraison = 5;
        }

        $sqlCommande = "INSERT INTO commande
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
        )";

        $stmtCommande = $pdo->prepare($sqlCommande);
        $stmtCommande->execute([
            "numero_commande" => $numero_commande,
            "date_prestation" => $date_prestation,
            "heure_livraison" => $heure_livraison,
            "prix_menu" => $prix_menu,
            "nombre_personne" => $nombre_personne,
            "prix_livraison" => $prix_livraison,
            "pret_materiel" => $pret_materiel
        ]);

        $sqlUtilisateur = "INSERT INTO commande_utilisateur
        (numero_commande, utilisateur_id)
        VALUES
        (:numero_commande, :utilisateur_id)";

        $stmtUtilisateur = $pdo->prepare($sqlUtilisateur);
        $stmtUtilisateur->execute([
            "numero_commande" => $numero_commande,
            "utilisateur_id" => $utilisateur_id
        ]);

        $sqlMenuCommande = "INSERT INTO commande_menu
        (numero_commande, menu_id)
        VALUES
        (:numero_commande, :menu_id)";

        $stmtMenuCommande = $pdo->prepare($sqlMenuCommande);
        $stmtMenuCommande->execute([
            "numero_commande" => $numero_commande,
            "menu_id" => $menu_id
        ]);

        echo "
        <script>
            alert('Commande enregistrée avec succès.');
            window.location.href = '../MesCommandes.html';
        </script>
        ";
        exit;

    } catch (PDOException $e) {
        echo "Erreur SQL : " . $e->getMessage();
    }
}
?>