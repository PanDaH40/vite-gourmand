<?php

$configPath = dirname(__DIR__, 2) . '/atlas_config.php';

$mongoDatabase = getenv('MONGO_DB') ?: 'vite_gourmand_analytics';

try {
    if (is_file($configPath)) {
        // OVH : connexion à MongoDB Atlas
        $config = require $configPath;

        $mongoDatabase = $config['database'];
        $mongo = new MongoDB\Driver\Manager($config['uri']);
    } else {
        // Docker : connexion MongoDB locale
        $mongoHost = getenv('MONGO_HOST') ?: 'mongodb';
        $mongoPort = getenv('MONGO_PORT') ?: '27017';

        $mongo = new MongoDB\Driver\Manager(
            "mongodb://{$mongoHost}:{$mongoPort}"
        );
    }
} catch (Throwable $e) {
    // Une panne MongoDB ne doit pas bloquer MySQL.
    error_log('Erreur MongoDB : ' . $e->getMessage());
    $mongo = null;
}