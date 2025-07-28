<?php

namespace AppWoyofal\Service;

use DevNoKage\Singleton;
use DevNoKage\Database;
use AppWoyofal\Entity\Achat;

class JournalisationService extends Singleton
{
    private Database $database;
    private string $logFile;

    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->logFile = __DIR__ . '/../../logs/woyofal_' . date('Y-m-d') . '.log';
        
        // Créer le répertoire de logs s'il n'existe pas
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }

    public function journaliserAchat(Achat $achat): void
    {
        // Journalisation en base de données
        $this->sauvegarderJournalBD($achat);
        
        // Journalisation dans un fichier
        $this->ecrireLogFichier($achat);
    }

    private function sauvegarderJournalBD(Achat $achat): bool
    {
        $sql = "INSERT INTO journal_achats (reference, numero_compteur, montant, nbre_kwt, 
                statut, adresse_ip, localisation, date_creation, heure_creation, 
                code_recharge, nom_client, tranche_numero, prix_unitaire) 
                VALUES (:reference, :numero_compteur, :montant, :nbre_kwt, :statut, 
                :adresse_ip, :localisation, :date_creation, :heure_creation, 
                :code_recharge, :nom_client, :tranche_numero, :prix_unitaire)";
        
        $stmt = $this->database->getConnexion()->prepare($sql);
        
        return $stmt->execute([
            'reference' => $achat->getReference(),
            'numero_compteur' => $achat->getNumeroCompteur(),
            'montant' => $achat->getMontant(),
            'nbre_kwt' => $achat->getNbreKwt(),
            'statut' => $achat->getStatut(),
            'adresse_ip' => $achat->getAdresseIp() ?: $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'localisation' => $achat->getLocalisation() ?: 'Senegal',
            'date_creation' => $achat->getDateAchat()->format('Y-m-d'),
            'heure_creation' => $achat->getHeureAchat()->format('H:i:s'),
            'code_recharge' => $achat->getCodeRecharge(),
            'nom_client' => $achat->getNomClient(),
            'tranche_numero' => $achat->getTrancheNumero(),
            'prix_unitaire' => $achat->getPrixUnitaire()
        ]);
    }

    private function ecrireLogFichier(Achat $achat): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $ip = $achat->getAdresseIp() ?: $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        $logEntry = sprintf(
            "[%s] %s | REF: %s | COMPTEUR: %s | MONTANT: %.2f | KWT: %.2f | STATUT: %s | IP: %s | LOC: %s | UA: %s" . PHP_EOL,
            $timestamp,
            $achat->getStatut(),
            $achat->getReference(),
            $achat->getNumeroCompteur(),
            $achat->getMontant(),
            $achat->getNbreKwt(),
            $achat->getStatut(),
            $ip,
            $achat->getLocalisation() ?: 'Senegal',
            $userAgent
        );

        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }

    public function obtenirStatistiques(string $dateDebut = '', string $dateFin = ''): array
    {
        $conditions = [];
        $params = [];

        if (!empty($dateDebut)) {
            $conditions[] = "date_creation >= :date_debut";
            $params['date_debut'] = $dateDebut;
        }

        if (!empty($dateFin)) {
            $conditions[] = "date_creation <= :date_fin";
            $params['date_fin'] = $dateFin;
        }

        $whereClause = empty($conditions) ? '' : 'WHERE ' . implode(' AND ', $conditions);

        // Statistiques générales
        $sql = "SELECT 
                    COUNT(*) as total_transactions,
                    COUNT(CASE WHEN statut = 'SUCCESS' THEN 1 END) as transactions_reussies,
                    COUNT(CASE WHEN statut = 'ECHEC' THEN 1 END) as transactions_echouees,
                    SUM(CASE WHEN statut = 'SUCCESS' THEN montant ELSE 0 END) as montant_total_succes,
                    SUM(CASE WHEN statut = 'SUCCESS' THEN nbre_kwt ELSE 0 END) as kwt_total_vendu,
                    AVG(CASE WHEN statut = 'SUCCESS' THEN montant ELSE NULL END) as montant_moyen
                FROM journal_achats $whereClause";

        $stmt = $this->database->getConnexion()->prepare($sql);
        $stmt->execute($params);
        $stats = $stmt->fetch();

        // Top 10 des compteurs par volume d'achat
        $sqlTop = "SELECT numero_compteur, nom_client, 
                   COUNT(*) as nb_achats,
                   SUM(montant) as montant_total,
                   SUM(nbre_kwt) as kwt_total
                   FROM journal_achats 
                   $whereClause AND statut = 'SUCCESS'
                   GROUP BY numero_compteur, nom_client 
                   ORDER BY montant_total DESC 
                   LIMIT 10";

        $stmtTop = $this->database->getConnexion()->prepare($sqlTop);
        $stmtTop->execute($params);
        $topCompteurs = $stmtTop->fetchAll();

        // Répartition par tranches
        $sqlTranches = "SELECT tranche_numero, prix_unitaire,
                        COUNT(*) as nb_transactions,
                        SUM(montant) as montant_total,
                        SUM(nbre_kwt) as kwt_total
                        FROM journal_achats 
                        $whereClause AND statut = 'SUCCESS'
                        GROUP BY tranche_numero, prix_unitaire 
                        ORDER BY tranche_numero";

        $stmtTranches = $this->database->getConnexion()->prepare($sqlTranches);
        $stmtTranches->execute($params);
        $repartitionTranches = $stmtTranches->fetchAll();

        return [
            'statistiques_generales' => $stats,
            'top_compteurs' => $topCompteurs,
            'repartition_tranches' => $repartitionTranches,
            'periode' => [
                'debut' => $dateDebut ?: 'Début',
                'fin' => $dateFin ?: 'Aujourd\'hui'
            ]
        ];
    }

    public function obtenirJournalPagine(int $page = 1, int $limite = 50, string $filtre = ''): array
    {
        $offset = ($page - 1) * $limite;
        $conditions = [];
        $params = [];

        if (!empty($filtre)) {
            $conditions[] = "(numero_compteur LIKE :filtre OR nom_client LIKE :filtre OR reference LIKE :filtre)";
            $params['filtre'] = "%$filtre%";
        }

        $whereClause = empty($conditions) ? '' : 'WHERE ' . implode(' AND ', $conditions);

        // Compter le total
        $sqlCount = "SELECT COUNT(*) as total FROM journal_achats $whereClause";
        $stmtCount = $this->database->getConnexion()->prepare($sqlCount);
        $stmtCount->execute($params);
        $total = $stmtCount->fetch()['total'];

        // Récupérer les données paginées
        $sql = "SELECT * FROM journal_achats $whereClause 
                ORDER BY date_creation DESC, heure_creation DESC 
                LIMIT :limite OFFSET :offset";

        $stmt = $this->database->getConnexion()->prepare($sql);
        $stmt->bindValue(':limite', $limite, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        
        $stmt->execute();
        $donnees = $stmt->fetchAll();

        return [
            'donnees' => $donnees,
            'pagination' => [
                'page_courante' => $page,
                'limite' => $limite,
                'total' => $total,
                'total_pages' => ceil($total / $limite),
                'offset' => $offset
            ]
        ];
    }

    public function nettoyerAncienLogs(int $joursConservation = 90): int
    {
        $dateExpiration = date('Y-m-d', strtotime("-$joursConservation days"));
        
        $sql = "DELETE FROM journal_achats WHERE date_creation < :date_expiration";
        $stmt = $this->database->getConnexion()->prepare($sql);
        $stmt->execute(['date_expiration' => $dateExpiration]);
        
        $lignesSupprimees = $stmt->rowCount();

        // Supprimer également les anciens fichiers de logs
        $this->supprimerAnciensLogsFiles($joursConservation);

        return $lignesSupprimees;
    }

    private function supprimerAnciensLogsFiles(int $joursConservation): void
    {
        $logDir = dirname($this->logFile);
        $dateExpiration = strtotime("-$joursConservation days");

        if (is_dir($logDir)) {
            $files = glob($logDir . '/woyofal_*.log');
            foreach ($files as $file) {
                if (filemtime($file) < $dateExpiration) {
                    unlink($file);
                }
            }
        }
    }
}
