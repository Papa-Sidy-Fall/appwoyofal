<?php

namespace AppWoyofal\Controller;

use DevNoKage\Response;
use AppWoyofal\Service\AchatService;
use AppWoyofal\Service\CompteurService;
use AppWoyofal\Service\TrancheService;
use AppWoyofal\Service\JournalisationService;

class WoyofalController
{
    private AchatService $achatService;
    private CompteurService $compteurService;
    private TrancheService $trancheService;
    private JournalisationService $journalisationService;

    public function __construct()
    {
        $this->achatService = AchatService::getInstance();
        $this->compteurService = CompteurService::getInstance();
        $this->trancheService = TrancheService::getInstance();
        $this->journalisationService = JournalisationService::getInstance();
    }

    /**
     * POST /api/woyofal/acheter
     * Effectuer un achat de crédit Woyofal
     */
    public function acheter(): void
    {
        try {
            // Récupérer les données JSON de la requête
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                Response::json([
                    'data' => null,
                    'statut' => 'error',
                    'code' => 400,
                    'message' => 'Données JSON invalides'
                ], 400);
                return;
            }

            // Validation des champs obligatoires
            if (empty($input['numeroCompteur']) || !isset($input['montant'])) {
                Response::json([
                    'data' => null,
                    'statut' => 'error',
                    'code' => 400,
                    'message' => 'Le numéro de compteur et le montant sont obligatoires'
                ], 400);
                return;
            }

            $numeroCompteur = $input['numeroCompteur'];
            $montant = (float)$input['montant'];

            // Validation du montant
            if ($montant <= 0) {
                Response::json([
                    'data' => null,
                    'statut' => 'error',
                    'code' => 400,
                    'message' => 'Le montant doit être supérieur à zéro'
                ], 400);
                return;
            }

            // Récupérer l'adresse IP et la localisation
            $adresseIp = $_SERVER['REMOTE_ADDR'] ?? '';
            $localisation = $input['localisation'] ?? 'Senegal';

            // Effectuer l'achat
            $resultat = $this->achatService->effectuerAchat($numeroCompteur, $montant, $adresseIp, $localisation);

            Response::json($resultat, $resultat['code']);

        } catch (\Exception $e) {
            Response::json([
                'data' => null,
                'statut' => 'error',
                'code' => 500,
                'message' => 'Erreur interne du serveur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/woyofal/compteur/{numero}
     * Vérifier l'existence d'un compteur
     */
    public function verifierCompteur(string $numero): void
    {
        try {
            $compteur = $this->compteurService->verifierExistenceCompteur($numero);
            
            if (!$compteur) {
                Response::json([
                    'data' => null,
                    'statut' => 'error',
                    'code' => 404,
                    'message' => 'Le numéro de compteur non retrouvé'
                ], 404);
                return;
            }

            Response::json([
                'data' => [
                    'compteur' => $compteur->toArray(),
                    'client' => $compteur->getClient()->toArray()
                ],
                'statut' => 'success',
                'code' => 200,
                'message' => 'Compteur trouvé'
            ], 200);

        } catch (\Exception $e) {
            Response::json([
                'data' => null,
                'statut' => 'error',
                'code' => 500,
                'message' => 'Erreur interne du serveur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/woyofal/tranches
     * Obtenir la liste des tranches de prix
     */
    public function obtenirTranches(): void
    {
        try {
            $tranches = $this->trancheService->obtenirTranchesActives();
            
            $tranchesArray = [];
            foreach ($tranches as $tranche) {
                $tranchesArray[] = $tranche->toArray();
            }

            Response::json([
                'data' => $tranchesArray,
                'statut' => 'success',
                'code' => 200,
                'message' => 'Tranches récupérées avec succès'
            ], 200);

        } catch (\Exception $e) {
            Response::json([
                'data' => null,
                'statut' => 'error',
                'code' => 500,
                'message' => 'Erreur interne du serveur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/woyofal/calculer-prix
     * Calculer le prix et les kWh pour un montant donné
     */
    public function calculerPrix(): void
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['montant'])) {
                Response::json([
                    'data' => null,
                    'statut' => 'error',
                    'code' => 400,
                    'message' => 'Le montant est obligatoire'
                ], 400);
                return;
            }

            $montant = (float)$input['montant'];

            if ($montant <= 0) {
                Response::json([
                    'data' => null,
                    'statut' => 'error',
                    'code' => 400,
                    'message' => 'Le montant doit être supérieur à zéro'
                ], 400);
                return;
            }

            $calcul = $this->trancheService->calculerRepartitionTranches($montant);

            Response::json([
                'data' => [
                    'montant' => $montant,
                    'kwtTotal' => $calcul['kwt_total'],
                    'repartition' => array_map(function($item) {
                        return [
                            'tranche' => $item['tranche']->toArray(),
                            'kwt' => $item['kwt'],
                            'montant' => $item['montant'],
                            'prixUnitaire' => $item['prix_unitaire']
                        ];
                    }, $calcul['repartition']),
                    'trancheFinale' => $calcul['tranche_finale'] ? $calcul['tranche_finale']->toArray() : null
                ],
                'statut' => 'success',
                'code' => 200,
                'message' => 'Calcul effectué avec succès'
            ], 200);

        } catch (\Exception $e) {
            Response::json([
                'data' => null,
                'statut' => 'error',
                'code' => 500,
                'message' => 'Erreur interne du serveur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/woyofal/historique/{numero?}
     * Obtenir l'historique des achats
     */
    public function obtenirHistorique(string $numero = ''): void
    {
        try {
            $limit = (int)($_GET['limit'] ?? 50);
            $achats = $this->achatService->obtenirHistoriqueAchats($numero, $limit);
            
            $achatsArray = [];
            foreach ($achats as $achat) {
                $achatsArray[] = $achat->toArray();
            }

            Response::json([
                'data' => $achatsArray,
                'statut' => 'success',
                'code' => 200,
                'message' => 'Historique récupéré avec succès'
            ], 200);

        } catch (\Exception $e) {
            Response::json([
                'data' => null,
                'statut' => 'error',
                'code' => 500,
                'message' => 'Erreur interne du serveur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/woyofal/achat/{reference}
     * Obtenir un achat par sa référence
     */
    public function obtenirAchatParReference(string $reference): void
    {
        try {
            $achat = $this->achatService->obtenirAchatParReference($reference);
            
            if (!$achat) {
                Response::json([
                    'data' => null,
                    'statut' => 'error',
                    'code' => 404,
                    'message' => 'Achat non trouvé'
                ], 404);
                return;
            }

            Response::json([
                'data' => $achat->toArray(),
                'statut' => 'success',
                'code' => 200,
                'message' => 'Achat trouvé'
            ], 200);

        } catch (\Exception $e) {
            Response::json([
                'data' => null,
                'statut' => 'error',
                'code' => 500,
                'message' => 'Erreur interne du serveur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/woyofal/statistiques
     * Obtenir les statistiques des achats
     */
    public function obtenirStatistiques(): void
    {
        try {
            $dateDebut = $_GET['dateDebut'] ?? '';
            $dateFin = $_GET['dateFin'] ?? '';
            
            $statistiques = $this->journalisationService->obtenirStatistiques($dateDebut, $dateFin);

            Response::json([
                'data' => $statistiques,
                'statut' => 'success',
                'code' => 200,
                'message' => 'Statistiques récupérées avec succès'
            ], 200);

        } catch (\Exception $e) {
            Response::json([
                'data' => null,
                'statut' => 'error',
                'code' => 500,
                'message' => 'Erreur interne du serveur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/woyofal/journal
     * Obtenir le journal des achats paginé
     */
    public function obtenirJournal(): void
    {
        try {
            $page = (int)($_GET['page'] ?? 1);
            $limite = (int)($_GET['limite'] ?? 50);
            $filtre = $_GET['filtre'] ?? '';
            
            $journal = $this->journalisationService->obtenirJournalPagine($page, $limite, $filtre);

            Response::json([
                'data' => $journal,
                'statut' => 'success',
                'code' => 200,
                'message' => 'Journal récupéré avec succès'
            ], 200);

        } catch (\Exception $e) {
            Response::json([
                'data' => null,
                'statut' => 'error',
                'code' => 500,
                'message' => 'Erreur interne du serveur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/woyofal/health
     * Vérifier l'état de santé du service
     */
    public function health(): void
    {
        try {
            // Vérifier la connexion à la base de données
            $compteurs = $this->compteurService->obtenirTousCompteurs();
            $tranches = $this->trancheService->obtenirTranchesActives();

            Response::json([
                'data' => [
                    'service' => 'AppWoyofal',
                    'version' => '1.0.0',
                    'status' => 'healthy',
                    'timestamp' => date('Y-m-d H:i:s'),
                    'database' => 'connected',
                    'compteurs_count' => count($compteurs),
                    'tranches_count' => count($tranches)
                ],
                'statut' => 'success',
                'code' => 200,
                'message' => 'Service opérationnel'
            ], 200);

        } catch (\Exception $e) {
            Response::json([
                'data' => [
                    'service' => 'AppWoyofal',
                    'version' => '1.0.0',
                    'status' => 'unhealthy',
                    'timestamp' => date('Y-m-d H:i:s'),
                    'error' => $e->getMessage()
                ],
                'statut' => 'error',
                'code' => 500,
                'message' => 'Service indisponible'
            ], 500);
        }
    }
}
