<?php

use DevNoKage\Router;
use AppWoyofal\Controller\WoyofalController;

// Routes API Woyofal

// Route racine pour tester
Router::get('/', function() {
    \DevNoKage\Response::json([
        'data' => 'AppWoyofal API est fonctionnel !',
        'statut' => 'success',
        'code' => 200,
        'message' => 'Bienvenue sur AppWoyofal API'
    ], 200);
});

// Route de diagnostic pour Render
Router::get('/debug', function() {
    $debug = [
        'php_version' => PHP_VERSION,
        'server_vars' => [
            'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? 'non défini',
            'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'] ?? 'non défini',
            'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? 'non défini',
        ],
        'env_vars' => [
            'ENVIRONMENT' => $_ENV['ENVIRONMENT'] ?? 'non défini',
            'DB_HOST' => isset($_ENV['DB_HOST']) ? 'défini' : 'non défini',
            'DB_NAME' => isset($_ENV['DB_NAME']) ? 'défini' : 'non défini',
        ],
        'autoload' => file_exists('/var/www/html/vendor/autoload.php') ? 'OK' : 'MANQUANT'
    ];
    
    \DevNoKage\Response::json([
        'data' => $debug,
        'statut' => 'success',
        'code' => 200,
        'message' => 'Diagnostic système'
    ], 200);
});

// Effectuer un achat
Router::post('/api/woyofal/acheter', function() {
    $controller = new WoyofalController();
    $controller->acheter();
});

// Vérifier un compteur
Router::get('/api/woyofal/compteur/{numero}', function($numero) {
    $controller = new WoyofalController();
    $controller->verifierCompteur($numero);
});

// Obtenir les tranches de prix
Router::get('/api/woyofal/tranches', function() {
    $controller = new WoyofalController();
    $controller->obtenirTranches();
});

// Calculer le prix pour un montant
Router::post('/api/woyofal/calculer-prix', function() {
    $controller = new WoyofalController();
    $controller->calculerPrix();
});

// Obtenir l'historique des achats
Router::get('/api/woyofal/historique', function() {
    $controller = new WoyofalController();
    $controller->obtenirHistorique();
});

// Obtenir l'historique d'un compteur spécifique
Router::get('/api/woyofal/historique/{numero}', function($numero) {
    $controller = new WoyofalController();
    $controller->obtenirHistorique($numero);
});

// Obtenir un achat par référence
Router::get('/api/woyofal/achat/{reference}', function($reference) {
    $controller = new WoyofalController();
    $controller->obtenirAchatParReference($reference);
});

// Obtenir les statistiques
Router::get('/api/woyofal/statistiques', function() {
    $controller = new WoyofalController();
    $controller->obtenirStatistiques();
});

// Obtenir le journal des achats
Router::get('/api/woyofal/journal', function() {
    $controller = new WoyofalController();
    $controller->obtenirJournal();
});

// Health check
Router::get('/api/woyofal/health', function() {
    $controller = new WoyofalController();
    $controller->health();
});

// Route racine de l'API pour la documentation
Router::get('/api/woyofal', function() {
    $documentation = [
        'service' => 'AppWoyofal API',
        'version' => '1.0.0',
        'description' => 'API pour la simulation du système de prépaiement d\'électricité de la Senelec',
        'endpoints' => [
            'POST /api/woyofal/acheter' => 'Effectuer un achat de crédit Woyofal',
            'GET /api/woyofal/compteur/{numero}' => 'Vérifier l\'existence d\'un compteur',
            'GET /api/woyofal/tranches' => 'Obtenir la liste des tranches de prix',
            'POST /api/woyofal/calculer-prix' => 'Calculer le prix et les kWh pour un montant',
            'GET /api/woyofal/historique/{numero?}' => 'Obtenir l\'historique des achats',
            'GET /api/woyofal/achat/{reference}' => 'Obtenir un achat par sa référence',
            'GET /api/woyofal/statistiques' => 'Obtenir les statistiques des achats',
            'GET /api/woyofal/journal' => 'Obtenir le journal des achats paginé',
            'GET /api/woyofal/health' => 'Vérifier l\'état de santé du service'
        ],
        'contact' => [
            'email' => 'dev@woyofal.sn',
            'documentation' => '/docs/api'
        ]
    ];

    \DevNoKage\Response::json([
        'data' => $documentation,
        'statut' => 'success',
        'code' => 200,
        'message' => 'Documentation API AppWoyofal'
    ], 200);
});
