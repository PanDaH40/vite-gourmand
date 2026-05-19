<?php

require "db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nom = $_POST["nom"];
    $prenom = $_POST["prenom"];
    $email = $_POST["email"];
    $telephone = $_POST["telephone"];
    $adresse = $_POST["adresse_postale"];
    $ville = $_POST["ville"];
    $pays = $_POST["pays"];
    $password = $_POST["password"];

    // Sécuriser le mot de passe en md5 pour que ca colle aux shéma sql, vu sur le manuel php function md5
    $passwordHash = md5($password); 

    try {

        $sql = "INSERT INTO utilisateur 
        (email, password, prenom, telephone, ville, pays, adresse_postale)
        VALUES (:email, :password, :prenom, :telephone, :ville, :pays, :adresse)";

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

        echo "Compte créé avec succès.";

    } catch (PDOException $e) {

        echo "Erreur : " . $e->getMessage();

    }

}