<?php

require_once __DIR__ . '/../app/config/bootstrap.php';

use DevNoKage\Database;

class MigrationManager
{
    private Database $database;
    private string $migrationPath;

    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->migrationPath = __DIR__;
    }

    public function runAllMigrations(): bool
    {
        echo "🚀 Démarrage des migrations pour AppWoyofal...\n\n";

        $migrations = [
            '001_create_clients_table.sql',
            '002_create_compteurs_table.sql', 
            '003_create_tranches_table.sql',
            '004_create_achats_table.sql',
            '005_create_journal_achats_table.sql'
        ];

        $success = true;

        foreach ($migrations as $migration) {
            echo "⏳ Exécution de $migration...\n";
            
            if ($this->runMigration($migration)) {
                echo "✅ $migration exécutée avec succès\n\n";
            } else {
                echo "❌ Erreur lors de l'exécution de $migration\n\n";
                $success = false;
            }
        }

        if ($success) {
            echo "🎉 Toutes les migrations ont été exécutées avec succès !\n";
            
            // Exécuter les seeders
            echo "\n🌱 Exécution des seeders...\n";
            $this->runSeeders();
        } else {
            echo "💥 Des erreurs sont survenues lors des migrations.\n";
        }

        return $success;
    }

    private function runMigration(string $migrationFile): bool
    {
        try {
            $sqlPath = $this->migrationPath . '/' . $migrationFile;
            
            if (!file_exists($sqlPath)) {
                throw new Exception("Fichier de migration non trouvé: $sqlPath");
            }

            $sql = file_get_contents($sqlPath);
            
            if ($sql === false) {
                throw new Exception("Impossible de lire le fichier: $sqlPath");
            }

            $this->database->getConnexion()->exec($sql);
            return true;

        } catch (Exception $e) {
            echo "Erreur: " . $e->getMessage() . "\n";
            return false;
        }
    }

    private function runSeeders(): void
    {
        try {
            echo "📝 Insertion des tranches par défaut...\n";
            $this->seedTranches();
            echo "✅ Tranches créées avec succès\n";

            echo "👥 Insertion des clients de test...\n";
            $this->seedClients();
            echo "✅ Clients de test créés avec succès\n";

            echo "⚡ Insertion des compteurs de test...\n";
            $this->seedCompteurs();
            echo "✅ Compteurs de test créés avec succès\n";

        } catch (Exception $e) {
            echo "❌ Erreur lors du seeding: " . $e->getMessage() . "\n";
        }
    }

    private function seedTranches(): void
    {
        $tranches = [
            [
                'numero' => 1,
                'seuil_min' => 0,
                'seuil_max' => 150,
                'prix_unitaire' => 79.99,
                'description' => 'Tranche 1: 0-150 kWh - Tarif social'
            ],
            [
                'numero' => 2,
                'seuil_min' => 150,
                'seuil_max' => 250,
                'prix_unitaire' => 89.99,
                'description' => 'Tranche 2: 150-250 kWh - Tarif normal'
            ],
            [
                'numero' => 3,
                'seuil_min' => 250,
                'seuil_max' => 0,
                'prix_unitaire' => 99.99,
                'description' => 'Tranche 3: >250 kWh - Tarif élevé'
            ]
        ];

        $sql = "INSERT INTO tranches (numero, seuil_min, seuil_max, prix_unitaire, description, actif) 
                VALUES (:numero, :seuil_min, :seuil_max, :prix_unitaire, :description, true)
                ON CONFLICT (numero) DO NOTHING";

        $stmt = $this->database->getConnexion()->prepare($sql);

        foreach ($tranches as $tranche) {
            $stmt->execute($tranche);
        }
    }

    private function seedClients(): void
    {
        $clients = [
            [
                'nom' => 'Diop',
                'prenom' => 'Amadou',
                'telephone' => '221771234567',
                'adresse' => 'Dakar, Plateau'
            ],
            [
                'nom' => 'Fall',
                'prenom' => 'Fatou',
                'telephone' => '221776543210',
                'adresse' => 'Thiès, Centre-ville'
            ],
            [
                'nom' => 'Ndiaye',
                'prenom' => 'Moussa',
                'telephone' => '221779876543',
                'adresse' => 'Saint-Louis, Sor'
            ],
            [
                'nom' => 'Seck',
                'prenom' => 'Aissatou',
                'telephone' => '221773456789',
                'adresse' => 'Kaolack, Médina'
            ]
        ];

        $sql = "INSERT INTO clients (nom, prenom, telephone, adresse) 
                VALUES (:nom, :prenom, :telephone, :adresse)
                ON CONFLICT (telephone) DO NOTHING";

        $stmt = $this->database->getConnexion()->prepare($sql);

        foreach ($clients as $client) {
            $stmt->execute($client);
        }
    }

    private function seedCompteurs(): void
    {
        // Récupérer les IDs des clients créés
        $sql = "SELECT id, nom, prenom FROM clients ORDER BY id LIMIT 4";
        $stmt = $this->database->getConnexion()->prepare($sql);
        $stmt->execute();
        $clients = $stmt->fetchAll();

        $compteurs = [];
        foreach ($clients as $index => $client) {
            $compteurs[] = [
                'numero' => 'WYF' . date('Ymd') . str_pad($index + 1, 6, '0', STR_PAD_LEFT),
                'client_id' => $client['id'],
                'statut' => 'ACTIF',
                'solde_actuel' => rand(0, 500) / 10 // Solde aléatoire entre 0 et 50 kWh
            ];
        }

        $sql = "INSERT INTO compteurs (numero, client_id, statut, solde_actuel) 
                VALUES (:numero, :client_id, :statut, :solde_actuel)
                ON CONFLICT (numero) DO NOTHING";

        $stmt = $this->database->getConnexion()->prepare($sql);

        foreach ($compteurs as $compteur) {
            $stmt->execute($compteur);
        }
    }

    public function checkTables(): void
    {
        echo "🔍 Vérification des tables créées...\n\n";

        $tables = ['clients', 'compteurs', 'tranches', 'achats', 'journal_achats'];
        
        foreach ($tables as $table) {
            $sql = "SELECT COUNT(*) as count FROM $table";
            try {
                $stmt = $this->database->getConnexion()->prepare($sql);
                $stmt->execute();
                $result = $stmt->fetch();
                echo "📋 Table '$table': {$result['count']} enregistrement(s)\n";
            } catch (Exception $e) {
                echo "❌ Erreur avec la table '$table': " . $e->getMessage() . "\n";
            }
        }
    }
}

// Exécution si appelé directement
if (php_sapi_name() === 'cli') {
    $manager = new MigrationManager();
    
    if (isset($argv[1]) && $argv[1] === 'check') {
        $manager->checkTables();
    } else {
        $manager->runAllMigrations();
        echo "\n";
        $manager->checkTables();
    }
}
