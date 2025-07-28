<?php

// Script de débogage pour tester les routes
require_once 'app/config/bootstrap.php';
require_once 'routes/woyofal.php';

// Simuler une requête
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['REQUEST_METHOD'] = 'GET';

echo "Test de la route: " . $_SERVER['REQUEST_URI'] . "\n";
echo "Méthode: " . $_SERVER['REQUEST_METHOD'] . "\n\n";

// Tester le Router
use DevNoKage\Router;

try {
    Router::resolve();
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
