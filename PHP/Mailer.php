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

    try {

        // Configuration SMTP Gmail
        $mail->isSMTP();

        $mail->Host = "smtp.gmail.com";
        $mail->SMTPAuth = true;

        $mail->Username =
            getenv("GMAIL_USER");

        $mail->Password =
            getenv("GMAIL_APP_PASSWORD");

        $mail->SMTPSecure =
            PHPMailer::ENCRYPTION_STARTTLS;

        $mail->Port = 587;
        $mail->CharSet = "UTF-8";


        // Expéditeur
        $mail->setFrom(
            getenv("GMAIL_USER"),
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
            $mail->AltBody =
                strip_tags($contenuHtml);
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