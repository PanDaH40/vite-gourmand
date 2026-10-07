<?php

// Paramètres communs pour les mots de passe
const PASSWORD_ITERATIONS = 600000;
const PASSWORD_PREFIX = 'P';

// Convertir des données binaires en Base64 compact
function passwordBase64(string $data): string
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

// Créer une empreinte sécurisée de 49 caractères
function creerHashPassword(string $password): string
{
    // Sel aléatoire de 9 octets = 12 caractères
    $salt = random_bytes(9);

    // PBKDF2-HMAC-SHA256 : 27 octets = 36 caractères
    $hash = hash_pbkdf2(
        'sha256',
        $password,
        $salt,
        PASSWORD_ITERATIONS,
        27,
        true
    );

    return PASSWORD_PREFIX
        . passwordBase64($salt)
        . passwordBase64($hash);
}

// Vérifier un mot de passe au nouveau format
function verifierHashPassword(
    string $password,
    string $hashStocke
): bool {
    if (
        strlen($hashStocke) !== 49 ||
        !str_starts_with($hashStocke, PASSWORD_PREFIX)
    ) {
        return false;
    }

    $saltEncode = substr($hashStocke, 1, 12);
    $salt = base64_decode(
        strtr($saltEncode, '-_', '+/'),
        true
    );

    if ($salt === false || strlen($salt) !== 9) {
        return false;
    }

    $hashCalcule = hash_pbkdf2(
        'sha256',
        $password,
        $salt,
        PASSWORD_ITERATIONS,
        27,
        true
    );

    $hashCalcule = PASSWORD_PREFIX
        . $saltEncode
        . passwordBase64($hashCalcule);

    return hash_equals($hashStocke, $hashCalcule);
}
