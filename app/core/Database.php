<?php

namespace DevNoKage;

use PDO;

class Database extends Singleton
{
    private ?PDO $pdo = null;
    private static array $configDefault = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ];

    public function __construct()
    {
        try {
            // Charger la configuration de la base de données
            $config = require_once __DIR__ . '/../config/database.php';
            
            // Utiliser Railway en production ou défaut en local
            $env = $_ENV['ENVIRONMENT'] ?? 'default';
            $dbConfig = isset($config[$env]) ? $config[$env] : $config['default'];
            
            $dsn = "pgsql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']}";
            
            $this->pdo = new PDO(
                $dsn,
                $dbConfig['username'],
                $dbConfig['password'],
                $dbConfig['options']
            );
        } catch (\PDOException $e) {
            die('Erreur de connexion à la base de données : ' . $e->getMessage());
        }
    }

    public function getConnexion(): PDO
    {
        return $this->pdo;
    }
}
