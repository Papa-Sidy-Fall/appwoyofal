<?php

namespace AppWoyofal\Service;

use DevNoKage\Singleton;
use DevNoKage\Database;
use AppWoyofal\Entity\Tranche;
use AppWoyofal\Interface\TrancheServiceInterface;

class TrancheService extends Singleton implements TrancheServiceInterface
{
    private Database $database;

    public function __construct()
    {
        $this->database = Database::getInstance();
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

    public function obtenirTrancheParNumero(int $numero): ?Tranche
    {
        $sql = "SELECT * FROM tranches WHERE numero = :numero AND actif = true";
        
        $stmt = $this->database->getConnexion()->prepare($sql);
        $stmt->execute(['numero' => $numero]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        $tranche = new Tranche();
        return $tranche->fromArray($data);
    }

    public function calculerRepartitionTranches(float $montant): array
    {
        $tranches = $this->obtenirTranchesActives();
        $repartition = [];
        $montantRestant = $montant;
        $kwtTotal = 0;

        foreach ($tranches as $tranche) {
            if ($montantRestant <= 0) break;

            $seuilMin = $tranche->getSeuilMin();
            $seuilMax = $tranche->getSeuilMax();
            $prixUnitaire = $tranche->getPrixUnitaire();

            if ($seuilMax > 0) {
                // Tranche avec limite
                $kwtMaxTranche = $seuilMax - $seuilMin;
                $montantMaxTranche = $kwtMaxTranche * $prixUnitaire;
                
                if ($montantRestant >= $montantMaxTranche) {
                    $kwtTranche = $kwtMaxTranche;
                    $montantTranche = $montantMaxTranche;
                    $montantRestant -= $montantMaxTranche;
                } else {
                    $kwtTranche = $montantRestant / $prixUnitaire;
                    $montantTranche = $montantRestant;
                    $montantRestant = 0;
                }
            } else {
                // Dernière tranche sans limite
                $kwtTranche = $montantRestant / $prixUnitaire;
                $montantTranche = $montantRestant;
                $montantRestant = 0;
            }

            if ($kwtTranche > 0) {
                $repartition[] = [
                    'tranche' => $tranche,
                    'kwt' => round($kwtTranche, 2),
                    'montant' => round($montantTranche, 2),
                    'prix_unitaire' => $prixUnitaire
                ];
                $kwtTotal += $kwtTranche;
            }
        }

        $dernierElement = end($repartition);
        return [
            'repartition' => $repartition,
            'kwt_total' => round($kwtTotal, 2),
            'montant_total' => $montant,
            'tranche_finale' => $dernierElement !== false ? $dernierElement : null
        ];
    }

    public function creerTranche(Tranche $tranche): bool
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

    public function mettreAJourTranche(Tranche $tranche): bool
    {
        $sql = "UPDATE tranches SET seuil_min = :seuil_min, seuil_max = :seuil_max, 
                prix_unitaire = :prix_unitaire, description = :description, 
                actif = :actif, date_modification = :date_modification 
                WHERE id = :id";
        
        $stmt = $this->database->getConnexion()->prepare($sql);
        
        return $stmt->execute([
            'seuil_min' => $tranche->getSeuilMin(),
            'seuil_max' => $tranche->getSeuilMax(),
            'prix_unitaire' => $tranche->getPrixUnitaire(),
            'description' => $tranche->getDescription(),
            'actif' => $tranche->getActif(),
            'date_modification' => (new \DateTime())->format('Y-m-d H:i:s'),
            'id' => $tranche->getId()
        ]);
    }

    public function obtenirToutesTouches(): array
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

    public function initialiserTranchesParDefaut(): bool
    {
        $tranchesDefaut = [
            ['numero' => 1, 'seuil_min' => 0, 'seuil_max' => 150, 'prix_unitaire' => 79.99, 'description' => 'Tranche 1: 0-150 kWh'],
            ['numero' => 2, 'seuil_min' => 150, 'seuil_max' => 250, 'prix_unitaire' => 89.99, 'description' => 'Tranche 2: 150-250 kWh'],
            ['numero' => 3, 'seuil_min' => 250, 'seuil_max' => 0, 'prix_unitaire' => 99.99, 'description' => 'Tranche 3: >250 kWh']
        ];

        $success = true;
        foreach ($tranchesDefaut as $trancheData) {
            $tranche = new Tranche();
            $tranche->setNumero($trancheData['numero']);
            $tranche->setSeuilMin($trancheData['seuil_min']);
            $tranche->setSeuilMax($trancheData['seuil_max']);
            $tranche->setPrixUnitaire($trancheData['prix_unitaire']);
            $tranche->setDescription($trancheData['description']);
            
            if (!$this->creerTranche($tranche)) {
                $success = false;
            }
        }

        return $success;
    }

    public function calculerKwtPourMontant(float $montant): float
    {
        $repartition = $this->calculerRepartitionTranches($montant);
        return $repartition['kwt_total'];
    }
}
