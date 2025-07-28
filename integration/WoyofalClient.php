<?php

/**
 * Client pour intégrer l'API AppWoyofal dans MAXITSA
 */
class WoyofalClient
{
    private string $baseUrl;
    private int $timeout;
    
    public function __construct(string $baseUrl, int $timeout = 30)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->timeout = $timeout;
    }

    /**
     * Effectuer un achat de crédit électrique
     */
    public function acheterCredit(string $numeroCompteur, float $montant, string $localisation = 'MAXITSA'): array
    {
        $data = [
            'numeroCompteur' => $numeroCompteur,
            'montant' => $montant,
            'localisation' => $localisation
        ];
        
        return $this->makeRequest('POST', '/api/woyofal/acheter', $data);
    }

    /**
     * Vérifier l'existence d'un compteur
     */
    public function verifierCompteur(string $numeroCompteur): array
    {
        return $this->makeRequest('GET', "/api/woyofal/compteur/{$numeroCompteur}");
    }

    /**
     * Obtenir les tranches tarifaires
     */
    public function obtenirTranches(): array
    {
        return $this->makeRequest('GET', '/api/woyofal/tranches');
    }

    /**
     * Calculer le prix pour un montant donné
     */
    public function calculerPrix(float $montant): array
    {
        $data = ['montant' => $montant];
        return $this->makeRequest('POST', '/api/woyofal/calculer-prix', $data);
    }

    /**
     * Obtenir l'historique des achats d'un compteur
     */
    public function obtenirHistorique(string $numeroCompteur = null, int $limite = 50): array
    {
        $endpoint = $numeroCompteur 
            ? "/api/woyofal/historique/{$numeroCompteur}"
            : "/api/woyofal/historique";
            
        $params = $limite !== 50 ? "?limite={$limite}" : '';
        
        return $this->makeRequest('GET', $endpoint . $params);
    }

    /**
     * Obtenir un achat par sa référence
     */
    public function obtenirAchatParReference(string $reference): array
    {
        return $this->makeRequest('GET', "/api/woyofal/achat/{$reference}");
    }

    /**
     * Vérifier l'état de santé de l'API
     */
    public function healthCheck(): array
    {
        return $this->makeRequest('GET', '/api/woyofal/health');
    }

    /**
     * Méthode privée pour effectuer les requêtes HTTP
     */
    private function makeRequest(string $method, string $endpoint, array $data = null): array
    {
        $url = $this->baseUrl . $endpoint;
        
        $curl = curl_init();
        
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
                'User-Agent: MAXITSA/1.0'
            ],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_FOLLOWLOCATION => true
        ];

        if ($method === 'POST') {
            $options[CURLOPT_POST] = true;
            if ($data) {
                $options[CURLOPT_POSTFIELDS] = json_encode($data);
            }
        }

        curl_setopt_array($curl, $options);
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        
        curl_close($curl);

        if ($error) {
            throw new Exception("Erreur cURL: {$error}");
        }

        $decodedResponse = json_decode($response, true);
        
        if ($httpCode >= 400) {
            $message = $decodedResponse['message'] ?? "Erreur HTTP {$httpCode}";
            throw new Exception("Erreur API Woyofal: {$message}", $httpCode);
        }

        return $decodedResponse;
    }
}
