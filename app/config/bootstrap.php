<?php

// Démarrage de l'application
require_once __DIR__ . '/../../vendor/autoload.php';

// Charger les variables d'environnement
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

// Configuration des erreurs
error_reporting(E_ALL);
ini_set('display_errors', $_ENV['APP_DEBUG'] ?? 0);

// Définir les constantes de base de données
define('DB_DRIVE', $_ENV['DB_DRIVE'] ?? 'pgsql');
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_PORT', $_ENV['DB_PORT'] ?? '5432');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'appwoyofal');
define('DB_USER', $_ENV['DB_USER'] ?? 'postgres');
define('DB_PASSWORD', $_ENV['DB_PASSWORD'] ?? '');

// Définir les constantes de l'application
define('APP_NAME', $_ENV['APP_NAME'] ?? 'AppWoyofal');
define('APP_VERSION', $_ENV['APP_VERSION'] ?? '1.0.0');
define('APP_ENV', $_ENV['APP_ENV'] ?? 'production');
define('APP_DEBUG', $_ENV['APP_DEBUG'] ?? false);
define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost');

// Configuration du fuseau horaire
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Africa/Dakar');

// Configuration des headers CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Access-Control-Max-Age: 86400');
    exit(0);
}

// Headers de sécurité
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Gérer les sessions si nécessaire
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Charger les helpers
require_once __DIR__ . '/helpers.php';

// Configuration des logs
if (!is_dir(__DIR__ . '/../../logs')) {
    mkdir(__DIR__ . '/../../logs', 0755, true);
}

// Gestionnaire d'erreurs personnalisé
set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return;
    }
    
    $logMessage = sprintf(
        "[%s] PHP Error: %s in %s on line %d",
        date('Y-m-d H:i:s'),
        $message,
        $file,
        $line
    );
    
    error_log($logMessage, 3, __DIR__ . '/../../logs/error_' . date('Y-m-d') . '.log');
    
    if (APP_DEBUG) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    }
});

// Gestionnaire d'exceptions personnalisé
set_exception_handler(function($exception) {
    $logMessage = sprintf(
        "[%s] Uncaught Exception: %s in %s on line %d\nStack trace:\n%s",
        date('Y-m-d H:i:s'),
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine(),
        $exception->getTraceAsString()
    );
    
    error_log($logMessage, 3, __DIR__ . '/../../logs/error_' . date('Y-m-d') . '.log');
    
    if (APP_DEBUG) {
        \DevNoKage\Response::json([
            'data' => [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            ],
            'statut' => 'error',
            'code' => 500,
            'message' => 'Erreur interne: ' . $exception->getMessage()
        ], 500);
    } else {
        \DevNoKage\Response::json([
            'data' => null,
            'statut' => 'error',
            'code' => 500,
            'message' => 'Erreur interne du serveur'
        ], 500);
    }
});
