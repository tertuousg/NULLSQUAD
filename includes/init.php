<?php
$possibleConfigFiles = [
    __DIR__ . '/../config/config.php',
    __DIR__ . '/../../config/config.php',
    '/var/www/html/config/config.php',
];

$configLoaded = false;

foreach ($possibleConfigFiles as $configFile) {
    if (is_file($configFile)) {
        require_once $configFile;
        $configLoaded = true;
        break;
    }
}

if (!$configLoaded) {
    if (!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
    if (!defined('DB_NAME')) define('DB_NAME', getenv('DB_NAME') ?: 'nullsquad');
    if (!defined('DB_USER')) define('DB_USER', getenv('DB_USER') ?: 'root');
    if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') ?: '');
    if (!defined('BASE_URL')) define('BASE_URL', getenv('BASE_URL') ?: '');
}

// keep the rest of your init code below this line
