
<?php

require_once __DIR__ . "/../vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Envoie un e-mail depuis Vite & Gourmand.
 */
function envoyerEmail(
    string $destinataire,
    string $sujet,
    string $contenuHtml,
    string $contenuTexte = ""
): bool {

    $mail = new PHPMailer(true);

    // Configuration privée OVH ou variables Docker
    $configPath = dirname(__DIR__, 2) . "/mail_config.php";

    if (is_file($configPath)) {
        // OVH
        $config = require $configPath;
        $gmailUser = $config["user"];
        $gmailPassword = $config["password"];
    } else {
        // Docker
        $gmailUser = getenv("GMAIL_USER");
        $gmailPassword = getenv("GMAIL_APP_PASSWORD");
    }

    try {

        // Configuration SMTP Gmail
        $mail->isSMTP();

        $mail->Host = "smtp.gmail.com";
        $mail->SMTPAuth = true;

        $mail->Username = $gmailUser;
        $mail->Password = $gmailPassword;

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = "UTF-8";

        // Expéditeur
        $mail->setFrom(
            $gmailUser,
            "Vite & Gourmand"
        );

        // Destinataire
        $mail->addAddress($destinataire);

        // Contenu
        $mail->isHTML(true);

        $mail->Subject = $sujet;
        $mail->Body = $contenuHtml;

        if ($contenuTexte !== "") {
            $mail->AltBody = $contenuTexte;
        } else {
            $mail->AltBody = strip_tags($contenuHtml);
        }

        // Envoi
        $mail->send();

        return true;

    } catch (Exception $e) {

        error_log(
            "Erreur envoi e-mail : " .
            $mail->ErrorInfo
        );

        return false;
    }
}
