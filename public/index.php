<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$basePath = (static function (): string {
    $configured = $_SERVER['LARAVEL_BASE_PATH'] ?? $_ENV['LARAVEL_BASE_PATH'] ?? getenv('LARAVEL_BASE_PATH');
    if (is_string($configured) && trim($configured) !== '') {
        return rtrim(trim($configured), "\\/");
    }

    $parentDir = dirname(__DIR__);
    $candidates = [$parentDir];

    foreach (glob($parentDir . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) ?: [] as $candidate) {
        $candidates[] = $candidate;
    }

    foreach (array_unique($candidates) as $candidate) {
        if (
            is_file($candidate . '/bootstrap/app.php')
            && is_file($candidate . '/vendor/autoload.php')
        ) {
            return $candidate;
        }
    }

    return $parentDir;
})();

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $basePath . '/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $basePath . '/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once $basePath . '/bootstrap/app.php')->handleRequest(Request::capture());
