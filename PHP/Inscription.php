<?php

require "db.php";
require_once "PasswordSecurity.php";
require_once "Mailer.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nom = $_POST["nom"] ?? "";
    $prenom = $_POST["prenom"] ?? "";
    $email = trim($_POST["email"] ?? "");
    $telephone = $_POST["telephone"] ?? "";
    $adresse = $_POST["adresse_postale"] ?? "";
    $ville = $_POST["ville"] ?? "";
    $pays = $_POST["pays"] ?? "";
    $password = $_POST["password"] ?? "";


    // Vérifier le format de l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        http_response_code(400);

        exit("Adresse email invalide.");
    }


    // Vérifier la sécurité du mot de passe
    if (
        strlen($password) < 10 ||
        !preg_match('/[A-Z]/', $password) ||
        !preg_match('/[a-z]/', $password) ||
        !preg_match('/[0-9]/', $password) ||
        !preg_match('/[^a-zA-Z0-9]/', $password)
    ) {

        http_response_code(400);

        exit(
            "Le mot de passe doit contenir au moins " .
            "10 caractères, une majuscule, une minuscule, " .
            "un chiffre et un caractère spécial."
        );
    }


    try {

        // Vérifier si l'adresse email existe déjà
        $sqlVerif = "
            SELECT utilisateur_id
            FROM utilisateur
            WHERE email = :email
            LIMIT 1
        ";

        $stmtVerif = $pdo->prepare($sqlVerif);

        $stmtVerif->execute([
            "email" => $email
        ]);


        if ($stmtVerif->fetch(PDO::FETCH_ASSOC)) {

            http_response_code(409);

            exit(
                "Cette adresse email est déjà utilisée."
            );
        }


        // Sécuriser le mot de passe avec PBKDF2
        $passwordHash =
            creerHashPassword($password);


        // Insérer le nouvel utilisateur
        $sql = "
            INSERT INTO utilisateur
            (
                email,
                password,
                prenom,
                telephone,
                ville,
                pays,
                adresse_postale
            )
            VALUES
            (
                :email,
                :password,
                :prenom,
                :telephone,
                :ville,
                :pays,
                :adresse
            )
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            "email" => $email,
            "password" => $passwordHash,
            "prenom" => $prenom,
            "telephone" => $telephone,
            "ville" => $ville,
            "pays" => $pays,
            "adresse" => $adresse
        ]);


        /**
         * Envoi du mail de bienvenue.
         *
         * Le compte reste créé même si le serveur
         * d'e-mail rencontre temporairement une erreur.
         */
        $prenomSecurise =
            htmlspecialchars(
                $prenom,
                ENT_QUOTES,
                "UTF-8"
            );


        $contenuHtml = "
            <h1>Bienvenue chez Vite & Gourmand</h1>

            <p>
                Bonjour {$prenomSecurise},
            </p>

            <p>
                Votre compte Vite & Gourmand
                a bien été créé.
            </p>

            <p>
                Vous pouvez désormais vous connecter
                et commander nos menus.
            </p>

            <p>
                À bientôt chez Vite & Gourmand !
            </p>
        ";


        $contenuTexte =
            "Bonjour " . $prenom . ",\n\n" .
            "Votre compte Vite & Gourmand a bien été créé.\n" .
            "Vous pouvez désormais vous connecter " .
            "et commander nos menus.\n\n" .
            "À bientôt chez Vite & Gourmand !";


        envoyerEmail(
            $email,
            "Bienvenue chez Vite & Gourmand",
            $contenuHtml,
            $contenuTexte
        );


        echo "Compte créé avec succès.";

    } catch (PDOException $e) {

        error_log(
            "Erreur inscription : " .
            $e->getMessage()
        );

        http_response_code(500);

        exit(
            "Une erreur est survenue lors de l'inscription."
        );
    }
}