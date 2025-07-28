<?php

namespace AppWoyofal\Repository;

use DevNoKage\Singleton;
use DevNoKage\Database;
use AppWoyofal\Entity\Tranche;

class TrancheRepository extends Singleton
{
    private Database $database;

    public function __construct()
    {
        $this->database = Database::getInstance();
    }

    public function creer(Tranche $tranche): bool
    {
        $sql = "INSERT INTO tranches (numero, seuil_min, seuil_max, prix_unitaire, description, actif, date_creation, date_modification) 
                VALUES (:numero, :seuil_min, :seuil_max, :prix_unitaire, :description, :actif, :date_creation, :date_modification)";
        
        $stmt = $this->database->getConnexion()->prepare($sql);
        
        return $stmt->execute([
            'numero' => $tranche->getNumero(),
            'seuil_min' => $tranche->getSeuilMin(),
            'seuil_max' => $tranche->getSeuilMax(),
            'prix_unitaire' => $tranche->getPrixUnitaire(),
            'description' => $tranche->getDescription(),
            'actif' => $tranche->getActif(),
            'date_creation' => $tranche->getDateCreation()->format('Y-m-d H:i:s'),
            'date_modification' => $tranche->getDateModification()->format('Y-m-d H:i:s')
        ]);
    }

    public function obtenirParId(int $id): ?Tranche
    {
        $sql = "SELECT * FROM tranches WHERE id = :id";
        
        $stmt = $this->database->getConnexion()->prepare($sql);
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        $tranche = new Tranche();
        return $tranche->fromArray($data);
    }

    public function obtenirParNumero(int $numero): ?Tranche
    {
        $sql = "SELECT * FROM tranches WHERE numero = :numero";
        
        $stmt = $this->database->getConnexion()->prepare($sql);
        $stmt->execute(['numero' => $numero]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        $tranche = new Tranche();
        return $tranche->fromArray($data);
    }

    public function obtenirTranchesActives(): array
    {
        $sql = "SELECT * FROM tranches WHERE actif = true ORDER BY numero ASC";
        
        $stmt = $this->database->getConnexion()->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll();

        $tranches = [];
        foreach ($results as $data) {
            $tranche = new Tranche();
            $tranche->fromArray($data);
            $tranches[] = $tranche;
        }

        return $tranches;
    }

    public function obtenirToutes(): array
    {
        $sql = "SELECT * FROM tranches ORDER BY numero ASC";
        
        $stmt = $this->database->getConnexion()->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll();

        $tranches = [];
        foreach ($results as $data) {
            $tranche = new Tranche();
            $tranche->fromArray($data);
            $tranches[] = $tranche;
        }

        return $tranches;
    }

    public function mettreAJour(Tranche $tranche): bool
    {
        $sql = "UPDATE tranches SET numero = :numero, seuil_min = :seuil_min, seuil_max = :seuil_max, 
                prix_unitaire = :prix_unitaire, description = :description, 
                actif = :actif, date_modification = :date_modification 
                WHERE id = :id";
        
        $stmt = $this->database->getConnexion()->prepare($sql);
        
        return $stmt->execute([
            'numero' => $tranche->getNumero(),
            'seuil_min' => $tranche->getSeuilMin(),
            'seuil_max' => $tranche->getSeuilMax(),
            'prix_unitaire' => $tranche->getPrixUnitaire(),
            'description' => $tranche->getDescription(),
            'actif' => $tranche->getActif(),
            'date_modification' => (new \DateTime())->format('Y-m-d H:i:s'),
            'id' => $tranche->getId()
        ]);
    }

    public function activerDesactiver(int $id, bool $actif): bool
    {
        $sql = "UPDATE tranches SET actif = :actif, date_modification = :date_modification 
                WHERE id = :id";
        
        $stmt = $this->database->getConnexion()->prepare($sql);
        
        return $stmt->execute([
            'actif' => $actif,
            'date_modification' => (new \DateTime())->format('Y-m-d H:i:s'),
            'id' => $id
        ]);
    }

    public function supprimer(int $id): bool
    {
        $sql = "DELETE FROM tranches WHERE id = :id";
        
        $stmt = $this->database->getConnexion()->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    public function obtenirTranchesPourMontant(float $montant): array
    {
        $sql = "SELECT * FROM tranches WHERE actif = true 
                AND (seuil_max = 0 OR :montant / prix_unitaire <= seuil_max)
                ORDER BY numero ASC";
        
        $stmt = $this->database->getConnexion()->prepare($sql);
        $stmt->execute(['montant' => $montant]);
        $results = $stmt->fetchAll();

        $tranches = [];
        foreach ($results as $data) {
            $tranche = new Tranche();
            $tranche->fromArray($data);
            $tranches[] = $tranche;
        }

        return $tranches;
    }

    public function verifierChevauchement(int $numero, float $seuilMin, float $seuilMax, int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as count FROM tranches 
                WHERE numero != :numero AND actif = true";

        $params = ['numero' => $numero];

        if ($excludeId !== null) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }

        if ($seuilMax > 0) {
            $sql .= " AND ((seuil_min < :seuil_max AND seuil_max > :seuil_min) OR seuil_max = 0)";
            $params['seuil_min'] = $seuilMin;
            $params['seuil_max'] = $seuilMax;
        } else {
            $sql .= " AND seuil_min >= :seuil_min";
            $params['seuil_min'] = $seuilMin;
        }

        $stmt = $this->database->getConnexion()->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();

        return $result['count'] > 0;
    }
}
