<?php

namespace AppWoyofal\Repository;

use DevNoKage\Singleton;
use DevNoKage\Database;
use AppWoyofal\Entity\Achat;

class AchatRepository extends Singleton
{
    private Database $database;

    public function __construct()
    {
        $this->database = Database::getInstance();
    }

    public function creer(Achat $achat): bool
    {
        $sql = "INSERT INTO achats (reference, code_recharge, numero_compteur, montant, nbre_kwt, 
                tranche_numero, prix_unitaire, nom_client, adresse_ip, localisation, statut, 
                date_achat, heure_achat) 
                VALUES (:reference, :code_recharge, :numero_compteur, :montant, :nbre_kwt, 
                :tranche_numero, :prix_unitaire, :nom_client, :adresse_ip, :localisation, 
                :statut, :date_achat, :heure_achat)";
        
        $stmt = $this->database->getConnexion()->prepare($sql);
        
        return $stmt->execute([
            'reference' => $achat->getReference(),
            'code_recharge' => $achat->getCodeRecharge(),
            'numero_compteur' => $achat->getNumeroCompteur(),
            'montant' => $achat->getMontant(),
            'nbre_kwt' => $achat->getNbreKwt(),
            'tranche_numero' => $achat->getTrancheNumero(),
            'prix_unitaire' => $achat->getPrixUnitaire(),
            'nom_client' => $achat->getNomClient(),
            'adresse_ip' => $achat->getAdresseIp(),
            'localisation' => $achat->getLocalisation(),
            'statut' => $achat->getStatut(),
            'date_achat' => $achat->getDateAchat()->format('Y-m-d'),
            'heure_achat' => $achat->getHeureAchat()->format('H:i:s')
        ]);
    }

    public function obtenirParReference(string $reference): ?Achat
    {
        $sql = "SELECT * FROM achats WHERE reference = :reference";
        
        $stmt = $this->database->getConnexion()->prepare($sql);
        $stmt->execute(['reference' => $reference]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        $achat = new Achat();
        return $achat->fromArray($data);
    }

    public function obtenirParId(int $id): ?Achat
    {
        $sql = "SELECT * FROM achats WHERE id = :id";
        
        $stmt = $this->database->getConnexion()->prepare($sql);
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        $achat = new Achat();
        return $achat->fromArray($data);
    }

    public function obtenirParCompteur(string $numeroCompteur, int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT * FROM achats WHERE numero_compteur = :numero_compteur 
                ORDER BY date_achat DESC, heure_achat DESC 
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->database->getConnexion()->prepare($sql);
        $stmt->bindValue(':numero_compteur', $numeroCompteur);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll();

        $achats = [];
        foreach ($results as $data) {
            $achat = new Achat();
            $achat->fromArray($data);
            $achats[] = $achat;
        }

        return $achats;
    }

    public function obtenirTous(int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT * FROM achats ORDER BY date_achat DESC, heure_achat DESC 
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->database->getConnexion()->prepare($sql);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll();

        $achats = [];
        foreach ($results as $data) {
            $achat = new Achat();
            $achat->fromArray($data);
            $achats[] = $achat;
        }

        return $achats;
    }

    public function obtenirParStatut(string $statut, int $limit = 50): array
    {
        $sql = "SELECT * FROM achats WHERE statut = :statut 
                ORDER BY date_achat DESC, heure_achat DESC 
                LIMIT :limit";
        
        $stmt = $this->database->getConnexion()->prepare($sql);
        $stmt->bindValue(':statut', $statut);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll();

        $achats = [];
        foreach ($results as $data) {
            $achat = new Achat();
            $achat->fromArray($data);
            $achats[] = $achat;
        }

        return $achats;
    }

    public function obtenirParPeriode(string $dateDebut, string $dateFin, int $limit = 100): array
    {
        $sql = "SELECT * FROM achats 
                WHERE date_achat BETWEEN :date_debut AND :date_fin 
                ORDER BY date_achat DESC, heure_achat DESC 
                LIMIT :limit";
        
        $stmt = $this->database->getConnexion()->prepare($sql);
        $stmt->bindValue(':date_debut', $dateDebut);
        $stmt->bindValue(':date_fin', $dateFin);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll();

        $achats = [];
        foreach ($results as $data) {
            $achat = new Achat();
            $achat->fromArray($data);
            $achats[] = $achat;
        }

        return $achats;
    }

    public function compterAchats(string $numeroCompteur = '', string $statut = ''): int
    {
        $sql = "SELECT COUNT(*) as count FROM achats";
        $conditions = [];
        $params = [];

        if (!empty($numeroCompteur)) {
            $conditions[] = "numero_compteur = :numero_compteur";
            $params['numero_compteur'] = $numeroCompteur;
        }

        if (!empty($statut)) {
            $conditions[] = "statut = :statut";
            $params['statut'] = $statut;
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $stmt = $this->database->getConnexion()->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();

        return (int)$result['count'];
    }

    public function obtenirStatistiquesParTranche(): array
    {
        $sql = "SELECT tranche_numero, prix_unitaire,
                COUNT(*) as nb_transactions,
                SUM(montant) as montant_total,
                SUM(nbre_kwt) as kwt_total,
                AVG(montant) as montant_moyen
                FROM achats 
                WHERE statut = 'SUCCESS'
                GROUP BY tranche_numero, prix_unitaire 
                ORDER BY tranche_numero";

        $stmt = $this->database->getConnexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function obtenirTopCompteurs(int $limit = 10): array
    {
        $sql = "SELECT numero_compteur, nom_client,
                COUNT(*) as nb_achats,
                SUM(montant) as montant_total,
                SUM(nbre_kwt) as kwt_total,
                AVG(montant) as montant_moyen
                FROM achats 
                WHERE statut = 'SUCCESS'
                GROUP BY numero_compteur, nom_client 
                ORDER BY montant_total DESC 
                LIMIT :limit";

        $stmt = $this->database->getConnexion()->prepare($sql);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function obtenirStatistiquesGenerales(): array
    {
        $sql = "SELECT 
                COUNT(*) as total_transactions,
                COUNT(CASE WHEN statut = 'SUCCESS' THEN 1 END) as transactions_reussies,
                COUNT(CASE WHEN statut = 'ECHEC' THEN 1 END) as transactions_echouees,
                SUM(CASE WHEN statut = 'SUCCESS' THEN montant ELSE 0 END) as montant_total_succes,
                SUM(CASE WHEN statut = 'SUCCESS' THEN nbre_kwt ELSE 0 END) as kwt_total_vendu,
                AVG(CASE WHEN statut = 'SUCCESS' THEN montant ELSE NULL END) as montant_moyen,
                MIN(date_achat) as premiere_transaction,
                MAX(date_achat) as derniere_transaction
                FROM achats";

        $stmt = $this->database->getConnexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function supprimerAnciensAchats(int $joursConservation = 365): int
    {
        $dateExpiration = date('Y-m-d', strtotime("-$joursConservation days"));
        
        $sql = "DELETE FROM achats WHERE date_achat < :date_expiration";
        $stmt = $this->database->getConnexion()->prepare($sql);
        $stmt->execute(['date_expiration' => $dateExpiration]);
        
        return $stmt->rowCount();
    }
}
