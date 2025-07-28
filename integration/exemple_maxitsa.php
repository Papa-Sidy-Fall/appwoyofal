<?php

/**
 * Exemple d'intégration dans votre application MAXITSA
 */

require_once 'WoyofalClient.php';
require_once 'WoyofalService.php';

// Configuration
$WOYOFAL_API_URL = 'https://votre-app.onrender.com'; // À remplacer par votre URL Render

// Initialisation du service
$woyofalService = new WoyofalService($WOYOFAL_API_URL);

// ===== EXEMPLE 1: Traitement d'un achat =====
function traiterAchatElectricite($numeroCompteur, $montant)
{
    global $woyofalService;
    
    // Paramètres de l'achat
    $parametres = [
        'numeroCompteur' => $numeroCompteur,
        'montant' => $montant,
        'localisation' => 'MAXITSA - Agence Principale'
    ];
    
    // Traiter l'achat
    $resultat = $woyofalService->traiterAchatCredit($parametres);
    
    if ($resultat['success']) {
        // Achat réussi - traiter le résultat
        $data = $resultat['data'];
        
        echo "✅ Achat réussi !\n";
        echo "Référence: {$data['reference']}\n";
        echo "Code de recharge: {$data['code_recharge']}\n";
        echo "kWh obtenus: {$data['kwh']}\n";
        echo "Client: {$data['client']}\n";
        
        // Ici vous pouvez:
        // - Enregistrer dans votre base MAXITSA
        // - Imprimer le reçu
        // - Envoyer SMS au client
        // - Mettre à jour le solde commission
        
        return [
            'success' => true,
            'code_recharge' => $data['code_recharge'],
            'reference' => $data['reference']
        ];
        
    } else {
        // Achat échoué
        echo "❌ Échec: {$resultat['message']}\n";
        
        // Gerer l'échec selon le code d'erreur
        switch ($resultat['code']) {
            case 'COMPTEUR_INVALIDE':
                // Compteur n'existe pas
                break;
            case 'ACHAT_ECHEC':
                // Problème lors de l'achat
                break;
            case 'ERREUR_TECHNIQUE':
                // Problème technique, réessayer plus tard
                break;
        }
        
        return ['success' => false, 'message' => $resultat['message']];
    }
}

// ===== EXEMPLE 2: Vérifier un compteur =====
function verifierCompteurElectricite($numeroCompteur)
{
    global $woyofalService;
    
    $resultat = $woyofalService->obtenirInfosCompteur($numeroCompteur);
    
    if ($resultat['success']) {
        $compteur = $resultat['data'];
        echo "Compteur trouvé: {$compteur['nom']} - {$compteur['adresse']}\n";
        return true;
    } else {
        echo "Compteur non trouvé\n";
        return false;
    }
}

// ===== EXEMPLE 3: Simuler un achat (calcul de prix) =====
function simulerAchatElectricite($montant)
{
    global $woyofalService;
    
    $simulation = $woyofalService->simulerAchat($montant);
    
    if ($simulation['success']) {
        $data = $simulation['data'];
        echo "Pour {$data['montant']} FCFA:\n";
        echo "- Vous obtiendrez: {$data['kwh_obtenu']} kWh\n";
        echo "- Prix par kWh: {$data['prix_kwh']} FCFA\n";
        echo "- Tranche tarifaire: {$data['tranche']}\n";
        return $data;
    }
    
    return null;
}

// ===== EXEMPLE 4: Intégration dans votre interface MAXITSA =====

/**
 * Fonction à appeler depuis votre interface de vente MAXITSA
 */
function venteElectriciteSenelec($numeroCompteur, $montant, $clientMaxitsa = null)
{
    global $woyofalService;
    
    try {
        // 1. Vérifier l'état de l'API
        if (!$woyofalService->verifierEtatAPI()) {
            return [
                'success' => false,
                'message' => 'Service Woyofal temporairement indisponible'
            ];
        }
        
        // 2. Simuler l'achat pour informer le client
        $simulation = $woyofalService->simulerAchat($montant);
        if (!$simulation['success']) {
            return $simulation;
        }
        
        // 3. Confirmer avec le client (dans votre interface)
        $kwh = $simulation['data']['kwh_obtenu'];
        echo "Confirmation: {$montant} FCFA = {$kwh} kWh. Continuer ? (o/n)\n";
        
        // 4. Effectuer l'achat
        $parametres = [
            'numeroCompteur' => $numeroCompteur,
            'montant' => $montant,
            'localisation' => 'MAXITSA'
        ];
        
        $achat = $woyofalService->traiterAchatCredit($parametres);
        
        if ($achat['success']) {
            // 5. Enregistrer la transaction dans MAXITSA
            $transactionMaxitsa = [
                'type' => 'ELECTRICITE_SENELEC',
                'reference_externe' => $achat['data']['reference'],
                'numero_compteur' => $numeroCompteur,
                'montant' => $montant,
                'commission' => $montant * 0.02, // 2% de commission par exemple
                'code_recharge' => $achat['data']['code_recharge'],
                'client_maxitsa' => $clientMaxitsa,
                'date_transaction' => date('Y-m-d H:i:s'),
                'statut' => 'SUCCESS'
            ];
            
            // Sauvegarder dans votre base MAXITSA
            // saveTransactionMaxitsa($transactionMaxitsa);
            
            return [
                'success' => true,
                'transaction' => $transactionMaxitsa,
                'message' => 'Transaction réussie'
            ];
        }
        
        return $achat;
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Erreur système: ' . $e->getMessage()
        ];
    }
}

// ===== TESTS =====
if (php_sapi_name() === 'cli') {
    echo "=== Tests d'intégration Woyofal - MAXITSA ===\n\n";
    
    // Test 1: Vérifier compteur
    echo "1. Test vérification compteur:\n";
    verifierCompteurElectricite('12345678');
    echo "\n";
    
    // Test 2: Simulation
    echo "2. Test simulation:\n";
    simulerAchatElectricite(5000);
    echo "\n";
    
    // Test 3: Achat complet
    echo "3. Test achat complet:\n";
    $resultat = traiterAchatElectricite('12345678', 5000);
    if ($resultat['success']) {
        echo "Code à donner au client: {$resultat['code_recharge']}\n";
    }
}
