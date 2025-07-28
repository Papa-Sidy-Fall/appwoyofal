<?php

namespace AppWoyofal\Repository;

use DevNoKage\Singleton;
use DevNoKage\Database;
use AppWoyofal\Entity\Compteur;

class CompteurRepository extends Singleton
{
    private Database $database;

    public function __construct()
    {
        $this->database = Database::getInstance();
    }

    public function creer(Compteur $compteur): bool
    {
        $sql = "INSERT INTO compteurs (numero, client_id, statut, solde_actuel, date_creation, date_modification) 
                VALUES (:numero, :client_id, :statut, :solde_actuel, :date_creation, :date_modification)";
        
        $stmt = $this->database->getConnexion()->prepare($sql);
        
        return $stmt->execute([
            'numero' => $compteur->getNumero(),
            'client_id' => $compteur->getClientId(),
            'statut' => $compteur->getStatut(),
            'solde_actuel' => $compteur->getSoldeActuel(),
            'date_creation' => $compteur->getDateCreation()->format('Y-m-d H:i:s'),
            'date_modification' => $compteur->getDateModification()->format('Y-m-d H:i:s')
        ]);
    }

    public function obtenirParNumero(string $numero): ?Compteur
    {
        $sql = "SELECT * FROM compteurs WHERE numero = :numero";
        
        $stmt = $this->database->getConnexion()->prepare($sql);
        $stmt->execute(['numero' => $numero]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        $compteur = new Compteur();
        return $compteur->fromArray($data);
    }

    public function obtenirParId(int $id): ?Compteur
    {
        $sql = "SELECT * FROM compteurs WHERE id = :id";
        
        $stmt = $this->database->getConnexion()->prepare($sql);
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        $compteur = new Compteur();
        return $compteur->fromArray($data);
    }

    public function obtenirParClientId(int $clientId): array
    {
        $sql = "SELECT * FROM compteurs WHERE client_id = :client_id ORDER BY date_creation DESC";
        
        $stmt = $this->database->getConnexion()->prepare($sql);
        $stmt->execute(['client_id' => $clientId]);
        $results = $stmt->fetchAll();

        $compteurs = [];
        foreach ($results as $data) {
            $compteur = new Compteur();
            $compteur->fromArray($data);
            $compteurs[] = $compteur;
        }

        return $compteurs;
    }

    public function obtenirTous(int $limit = 100, int $offset = 0): array
    {
        $sql = "SELECT * FROM compteurs ORDER BY date_creation DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->database->getConnexion()->prepare($sql);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll();

        $compteurs = [];
        foreach ($results as $data) {
            $compteur = new Compteur();
            $compteur->fromArray($data);
            $compteurs[] = $compteur;
        }

        return $compteurs;
    }

    public function mettreAJour(Compteur $compteur): bool
    {
        $sql = "UPDATE compteurs SET client_id = :client_id, statut = :statut, 
                solde_actuel = :solde_actuel, dernier_achat = :dernier_achat,
                date_modification = :date_modification 
                WHERE id = :id";
        
        $stmt = $this->database->getConnexion()->prepare($sql);
        
        return $stmt->execute([
            'client_id' => $compteur->getClientId(),
            'statut' => $compteur->getStatut(),
            'solde_actuel' => $compteur->getSoldeActuel(),
            'dernier_achat' => $compteur->getDernierAchat()?->format('Y-m-d H:i:s'),
            'date_modification' => (new \DateTime())->format('Y-m-d H:i:s'),
            'id' => $compteur->getId()
        ]);
    }

    public function mettreAJourSolde(string $numero, float $nouveauSolde): bool
    {
        $sql = "UPDATE compteurs SET solde_actuel = :solde, dernier_achat = :dernier_achat, 
                date_modification = :date_modification WHERE numero = :numero";
        
        $stmt = $this->database->getConnexion()->prepare($sql);
        
        return $stmt->execute([
            'solde' => $nouveauSolde,
            'dernier_achat' => (new \DateTime())->format('Y-m-d H:i:s'),
            'date_modification' => (new \DateTime())->format('Y-m-d H:i:s'),
            'numero' => $numero
        ]);
    }

    public function changerStatut(string $numero, string $statut): bool
    {
        $sql = "UPDATE compteurs SET statut = :statut, date_modification = :date_modification 
                WHERE numero = :numero";
        
        $stmt = $this->database->getConnexion()->prepare($sql);
        
        return $stmt->execute([
            'statut' => $statut,
            'date_modification' => (new \DateTime())->format('Y-m-d H:i:s'),
            'numero' => $numero
        ]);
    }

    public function obtenirCompteurActifs(): array
    {
        $sql = "SELECT * FROM compteurs WHERE statut = 'ACTIF' ORDER BY date_creation DESC";
        
        $stmt = $this->database->getConnexion()->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll();

        $compteurs = [];
        foreach ($results as $data) {
            $compteur = new Compteur();
            $compteur->fromArray($data);
            $compteurs[] = $compteur;
        }

        return $compteurs;
    }

    public function verifierExistence(string $numero): bool
    {
        $sql = "SELECT COUNT(*) as count FROM compteurs WHERE numero = :numero";
        
        $stmt = $this->database->getConnexion()->prepare($sql);
        $stmt->execute(['numero' => $numero]);
        $result = $stmt->fetch();

        return $result['count'] > 0;
    }
}
