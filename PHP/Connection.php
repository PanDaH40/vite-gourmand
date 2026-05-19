<?php
session_start();

require __DIR__ . "/db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (empty($email) || empty($password)) {
        die("Veuillez remplir tous les champs.");
    }

    try {

        $sql = "SELECT * FROM utilisateur WHERE email = :email LIMIT 1";
        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            "email" => $email
        ]);

        $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$utilisateur) {
            die("Aucun compte trouvé avec cette adresse email.");
        }

        if (md5($password) === $utilisateur["password"]) {

    $_SESSION["utilisateur_id"] = $utilisateur["utilisateur_id"];
    $_SESSION["email"] = $utilisateur["email"];
    $_SESSION["prenom"] = $utilisateur["prenom"];

    echo "
    <script>
    localStorage.setItem('prenomUtilisateur', '" . htmlspecialchars($utilisateur["prenom"], ENT_QUOTES) . "');
    window.location.href = '../PagePrincipale.html';
    </script>
    ";
    exit;

} else {
    echo "Mot de passe incorrect.";
}

    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }
}
?>