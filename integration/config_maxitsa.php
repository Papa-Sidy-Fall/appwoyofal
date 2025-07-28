<?php

/**
 * Configuration d'intégration Woyofal pour MAXITSA
 */

return [
    // Configuration API Woyofal
    'woyofal' => [
        'api_url' => env('WOYOFAL_API_URL', 'https://votre-app.onrender.com'),
        'timeout' => 30,
        'retry_attempts' => 3,
        'retry_delay' => 2, // secondes
    ],
    
    // Configuration commissions MAXITSA
    'commissions' => [
        'electricite_senelec' => [
            'pourcentage' => 2.0, // 2%
            'minimum' => 50,      // 50 FCFA minimum
            'maximum' => 500,     // 500 FCFA maximum
        ]
    ],
    
    // Configuration métier
    'limites' => [
        'montant_minimum' => 100,    // 100 FCFA
        'montant_maximum' => 100000, // 100 000 FCFA
    ],
    
    // Messages d'erreur personnalisés
    'messages' => [
        'COMPTEUR_INVALIDE' => 'Numéro de compteur introuvable. Vérifiez le numéro.',
        'MONTANT_INSUFFISANT' => 'Montant trop faible. Minimum 100 FCFA.',
        'MONTANT_TROP_ELEVE' => 'Montant trop élevé. Maximum 100 000 FCFA.',
        'API_INDISPONIBLE' => 'Service temporairement indisponible. Réessayez plus tard.',
        'ERREUR_TECHNIQUE' => 'Erreur technique. Contactez le support.',
    ],
    
    // Configuration logs
    'logging' => [
        'enabled' => true,
        'level' => 'info', // debug, info, warning, error
        'file' => 'logs/woyofal_transactions.log',
    ]
];
