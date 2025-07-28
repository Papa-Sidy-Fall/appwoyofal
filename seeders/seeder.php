<?php

require_once __DIR__ . '/../vendor/autoload.php';

use DevNoKage\Database;

// Configuration de la base de données
$database = Database::getInstance();

try {
    echo "=== Exécution des seeders ===" . PHP_EOL;
    
    // Liste des fichiers de seeders à exécuter
    $seedFiles = [
        '001_seed_tranches.sql',
        '002_seed_clients.sql',
        '003_seed_compteurs.sql'
    ];
    
    foreach ($seedFiles as $file) {
        $filePath = __DIR__ . '/' . $file;
        
        if (!file_exists($filePath)) {
            echo "Erreur: Le fichier $file n'existe pas" . PHP_EOL;
            continue;
        }
        
        echo "Exécution de $file..." . PHP_EOL;
        
        $sql = file_get_contents($filePath);
        
        // Séparer les requêtes par point-virgule
        $queries = array_filter(array_map('trim', explode(';', $sql)));
        
        foreach ($queries as $query) {
            if (!empty($query) && !str_starts_with(trim($query), '--')) {
                try {
                    $database->getConnexion()->exec($query);
                } catch (PDOException $e) {
                    echo "Erreur lors de l'exécution d'une requête: " . $e->getMessage() . PHP_EOL;
                }
            }
        }
        
        echo "✓ $file exécuté avec succès" . PHP_EOL;
    }
    
    echo "=== Seeders terminés ===" . PHP_EOL;
    
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage() . PHP_EOL;
    exit(1);
}
