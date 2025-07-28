<?php

namespace AppWoyofal\Service;

use DevNoKage\Singleton;
use DevNoKage\Database;
use AppWoyofal\Entity\Achat;
use AppWoyofal\Entity\Compteur;
use AppWoyofal\Interface\AchatServiceInterface;
use AppWoyofal\Interface\CompteurServiceInterface;
use AppWoyofal\Interface\TrancheServiceInterface;
use AppWoyofal\Interface\JournalisationServiceInterface;

class AchatService extends Singleton implements AchatServiceInterface
{
    private Database $database;
    private CompteurService $compteurService;
    private TrancheService $trancheService;
    private JournalisationService $journalisationService;

    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->compteurService = CompteurService::getInstance();
        $this->trancheService = TrancheService::getInstance();
        $this->journalisationService = JournalisationService::getInstance();
    }

    public function effectuerAchat(string $numeroCompteur, float $montant, string $adresseIp = '', string $localisation = ''): array
    {
        try {
            // Validation des entrées
            if (empty($numeroCompteur) || $montant <= 0) {
                throw new \InvalidArgumentException('Numéro de compteur et montant sont obligatoires et doivent être valides');
            }

            // Vérifier l'existence du compteur
            $compteur = $this->compteurService->verifierExistenceCompteur($numeroCompteur);
            if (!$compteur) {
                $this->journaliserEchec($numeroCompteur, $montant, $adresseIp, $localisation, 'Compteur non trouvé');
                return $this->creerReponseErreur('Le numéro de compteur non retrouvé', 404);
            }

            // Vérifier que le compteur est actif
            if (!$compteur->isActif()) {
                $this->journaliserEchec($numeroCompteur, $montant, $adresseIp, $localisation, 'Compteur inactif');
                return $this->creerReponseErreur('Le compteur est inactif', 400);
            }

            // Calculer la répartition par tranches
            $calculTranches = $this->trancheService->calculerRepartitionTranches($montant);
            
            if (empty($calculTranches['repartition'])) {
                $this->journaliserEchec($numeroCompteur, $montant, $adresseIp, $localisation, 'Aucune tranche applicable');
                return $this->creerReponseErreur('Aucune tranche de prix applicable', 500);
            }

            // Créer l'achat
            $achat = new Achat();
            $achat->setNumeroCompteur($numeroCompteur);
            $achat->setMontant($montant);
            $achat->setNbreKwt($calculTranches['kwt_total']);
            $achat->setNomClient($compteur->getClient()->getNomComplet());
            $achat->setAdresseIp($adresseIp);
            $achat->setLocalisation($localisation);

            // Utiliser la tranche finale pour les informations de prix
            $trancheFinale = $calculTranches['tranche_finale'];
            if ($trancheFinale) {
                $achat->setTrancheNumero($trancheFinale['tranche']->getNumero());
                $achat->setPrixUnitaire($trancheFinale['prix_unitaire']);
            }

            // Sauvegarder l'achat
            if ($this->sauvegarderAchat($achat)) {
                // Mettre à jour le solde du compteur
                $nouveauSolde = $compteur->getSoldeActuel() + $calculTranches['kwt_total'];
                $this->compteurService->mettreAJourSolde($numeroCompteur, $nouveauSolde);

                // Journaliser le succès
                $this->journalisationService->journaliserAchat($achat);

                return $this->creerReponseSucces($achat, $compteur);
            } else {
                throw new \Exception('Erreur lors de la sauvegarde de l\'achat');
            }

        } catch (\Exception $e) {
            $this->journaliserEchec($numeroCompteur, $montant, $adresseIp, $localisation, $e->getMessage());
            return $this->creerReponseErreur('Erreur lors du traitement de l\'achat: ' . $e->getMessage(), 500);
        }
    }

    private function sauvegarderAchat(Achat $achat): bool
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

    private function journaliserEchec(string $numeroCompteur, float $montant, string $adresseIp, string $localisation, string $raison): void
    {
        $achat = new Achat();
        $achat->setNumeroCompteur($numeroCompteur);
        $achat->setMontant($montant);
        $achat->setAdresseIp($adresseIp);
        $achat->setLocalisation($localisation);
        $achat->marquerEchec($raison);
        
        $this->sauvegarderAchat($achat);
        $this->journalisationService->journaliserAchat($achat);
    }

    private function creerReponseSucces(Achat $achat, Compteur $compteur): array
    {
        return [
            'data' => [
                'compteur' => $achat->getNumeroCompteur(),
                'reference' => $achat->getReference(),
                'code' => $achat->getCodeRecharge(),
                'date' => $achat->getDateAchat()->format('Y-m-d H:i:s'),
                'tranche' => $achat->getTrancheNumero(),
                'prix' => $achat->getPrixUnitaire(),
                'nbreKwt' => $achat->getNbreKwt(),
                'client' => $achat->getNomClient()
            ],
            'statut' => 'success',
            'code' => 200,
            'message' => 'Achat effectué avec succès'
        ];
    }

    private function creerReponseErreur(string $message, int $code): array
    {
        return [
            'data' => null,
            'statut' => 'error',
            'code' => $code,
            'message' => $message
        ];
    }

    public function obtenirHistoriqueAchats(string $numeroCompteur = '', int $limit = 50): array
    {
        $sql = "SELECT * FROM achats";
        $params = [];

        if (!empty($numeroCompteur)) {
            $sql .= " WHERE numero_compteur = :numero_compteur";
            $params['numero_compteur'] = $numeroCompteur;
        }

        $sql .= " ORDER BY date_achat DESC, heure_achat DESC LIMIT :limit";
        
        $stmt = $this->database->getConnexion()->prepare($sql);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        
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

    public function obtenirAchatParReference(string $reference): ?Achat
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
}
