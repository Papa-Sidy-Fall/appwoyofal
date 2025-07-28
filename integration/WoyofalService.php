<?php

/**
 * Service d'intégration Woyofal pour MAXITSA
 * Gère la logique métier et les transactions
 */
class WoyofalService
{
    private WoyofalClient $client;
    private $logger; // Votre système de log MAXITSA
    
    public function __construct(string $apiUrl, $logger = null)
    {
        $this->client = new WoyofalClient($apiUrl);
        $this->logger = $logger;
    }

    /**
     * Traiter un achat de crédit électrique
     */
    public function traiterAchatCredit(array $parametres): array
    {
        try {
            // 1. Validation des paramètres
            $this->validerParametresAchat($parametres);
            
            // 2. Vérifier le compteur avant l'achat
            $compteur = $this->client->verifierCompteur($parametres['numeroCompteur']);
            
            if ($compteur['statut'] !== 'success') {
                return [
                    'success' => false,
                    'message' => 'Compteur non trouvé',
                    'code' => 'COMPTEUR_INVALIDE'
                ];
            }

            // 3. Calculer le prix avant l'achat (optionnel)
            $calcul = $this->client->calculerPrix($parametres['montant']);
            
            // 4. Effectuer l'achat
            $achat = $this->client->acheterCredit(
                $parametres['numeroCompteur'],
                $parametres['montant'],
                $parametres['localisation'] ?? 'MAXITSA'
            );

            if ($achat['statut'] === 'success') {
                // 5. Logger la transaction réussie
                $this->logTransaction('SUCCESS', $achat);
                
                return [
                    'success' => true,
                    'data' => [
                        'reference' => $achat['data']['reference'],
                        'code_recharge' => $achat['data']['code'],
                        'compteur' => $achat['data']['compteur'],
                        'montant' => $parametres['montant'],
                        'kwh' => $achat['data']['nbreKwt'],
                        'prix_kwh' => $achat['data']['prix'],
                        'tranche' => $achat['data']['tranche'],
                        'client' => $achat['data']['client'],
                        'date' => $achat['data']['date']
                    ],
                    'message' => 'Achat effectué avec succès'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $achat['message'] ?? 'Erreur lors de l\'achat',
                    'code' => 'ACHAT_ECHEC'
                ];
            }

        } catch (Exception $e) {
            // Logger l'erreur
            $this->logTransaction('ERROR', null, $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Erreur technique: ' . $e->getMessage(),
                'code' => 'ERREUR_TECHNIQUE'
            ];
        }
    }

    /**
     * Obtenir les informations d'un compteur
     */
    public function obtenirInfosCompteur(string $numeroCompteur): array
    {
        try {
            $response = $this->client->verifierCompteur($numeroCompteur);
            
            if ($response['statut'] === 'success') {
                return [
                    'success' => true,
                    'data' => $response['data']
                ];
            }
            
            return [
                'success' => false,
                'message' => $response['message']
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la vérification: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtenir les tranches tarifaires
     */
    public function obtenirTarifsTranches(): array
    {
        try {
            $response = $this->client->obtenirTranches();
            return $response['data'] ?? [];
        } catch (Exception $e) {
            $this->logTransaction('ERROR', null, 'Erreur récupération tranches: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Calculer le coût d'un achat avant transaction
     */
    public function simulerAchat(float $montant): array
    {
        try {
            $response = $this->client->calculerPrix($montant);
            
            if ($response['statut'] === 'success') {
                return [
                    'success' => true,
                    'data' => [
                        'montant' => $montant,
                        'kwh_obtenu' => $response['data']['nbreKwt'],
                        'prix_kwh' => $response['data']['prix'],
                        'tranche' => $response['data']['tranche']
                    ]
                ];
            }
            
            return ['success' => false, 'message' => $response['message']];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur simulation: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtenir l'historique des achats
     */
    public function obtenirHistoriqueAchats(string $numeroCompteur = null, int $limite = 20): array
    {
        try {
            $response = $this->client->obtenirHistorique($numeroCompteur, $limite);
            
            if ($response['statut'] === 'success') {
                return [
                    'success' => true,
                    'data' => $response['data']
                ];
            }
            
            return ['success' => false, 'message' => $response['message']];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur historique: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Vérifier l'état de l'API
     */
    public function verifierEtatAPI(): bool
    {
        try {
            $response = $this->client->healthCheck();
            return $response['statut'] === 'success';
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Validation des paramètres d'achat
     */
    private function validerParametresAchat(array $parametres): void
    {
        if (empty($parametres['numeroCompteur'])) {
            throw new Exception('Numéro de compteur requis');
        }

        if (empty($parametres['montant']) || $parametres['montant'] <= 0) {
            throw new Exception('Montant invalide');
        }

        if (strlen($parametres['numeroCompteur']) !== 8) {
            throw new Exception('Numéro de compteur doit faire 8 caractères');
        }

        if ($parametres['montant'] < 100) {
            throw new Exception('Montant minimum : 100 FCFA');
        }
    }

    /**
     * Logger les transactions
     */
    private function logTransaction(string $status, ?array $data, string $error = null): void
    {
        if ($this->logger) {
            $logData = [
                'service' => 'Woyofal',
                'status' => $status,
                'timestamp' => date('Y-m-d H:i:s'),
                'data' => $data,
                'error' => $error
            ];
            
            // Adapter selon votre système de log MAXITSA
            $this->logger->info('Woyofal Transaction', $logData);
        }
    }
}
