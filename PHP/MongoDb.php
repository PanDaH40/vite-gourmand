<?php

$mongoHost = getenv('MONGO_HOST') ?: 'mongodb';
$mongoPort = getenv('MONGO_PORT') ?: '27017';
$mongoDatabase = getenv('MONGO_DB') ?: 'vite_gourmand_analytics';

try {
    $mongo = new MongoDB\Driver\Manager(
        "mongodb://{$mongoHost}:{$mongoPort}"
    );
} catch (Throwable $e) {
    // Une panne MongoDB ne doit pas bloquer MySQL.
    error_log('Erreur MongoDB : ' . $e->getMessage());
    $mongo = null;
}