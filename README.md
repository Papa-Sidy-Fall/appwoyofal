# AppWoyofal - API de Simulation de Prépaiement d'Électricité

## Description

AppWoyofal simule le système de prépaiement d'électricité de la Senelec avec un système de tranches progressives. L'application génère des codes de recharge et gère la journalisation complète des transactions.

## Architecture

L'application respecte les principes SOLID et utilise le pattern Singleton pour optimiser les performances :

### Structure du Projet

```
project-initializer/
├── app/
│   ├── config/                 # Configuration de l'application
│   └── core/                   # Classes de base (Singleton, Database, etc.)
├── src/
│   ├── Controller/             # Contrôleurs API
│   ├── Entity/                 # Entités métier
│   ├── Service/                # Services métier (avec Singleton)
│   ├── Repository/             # Accès aux données
│   └── Interface/              # Interfaces pour SOLID
├── migration/                  # Scripts de migration PostgreSQL
├── routes/                     # Définition des routes API
└── public/                     # Point d'entrée web
```

### Principes SOLID Appliqués

1. **Single Responsibility Principle** : Chaque classe a une responsabilité unique
2. **Open/Closed Principle** : Extensions via interfaces sans modification du code existant
3. **Liskov Substitution Principle** : Les implémentations sont interchangeables
4. **Interface Segregation Principle** : Interfaces spécialisées par fonctionnalité
5. **Dependency Inversion Principle** : Dépendance aux abstractions, pas aux implémentations

## Installation

### Prérequis

- Docker et Docker Compose
- PHP 8.2+
- PostgreSQL 15+
- Composer

### Installation en Local

1. **Cloner le projet**
```bash
git clone <repository-url>
cd appwoyofal
```

2. **Configuration de l'environnement**
```bash
cp project-initializer/.env.exemple project-initializer/.env
# Modifier les variables selon vos besoins
```

3. **Démarrage avec Docker**
```bash
docker-compose up -d
```

4. **Accès aux services**
- Application : http://localhost:8080
- API Documentation : http://localhost:8080/api/woyofal
- PgAdmin : http://localhost:8081 (admin@woyofal.sn / admin123)

### Déploiement sur Railway

1. **Préparer le projet**
```bash
# Build de l'image Docker
docker build -t appwoyofal .
docker tag appwoyofal your-registry/appwoyofal:latest
docker push your-registry/appwoyofal:latest
```

2. **Configuration Railway**
```bash
# Variables d'environnement à configurer
ENVIRONMENT=railway
PGHOST=<railway-postgres-host>
PGPORT=5432
PGDATABASE=<railway-database-name>
PGUSER=<railway-user>
PGPASSWORD=<railway-password>
```

3. **Base de données Railway**
- Créer une base PostgreSQL sur Railway
- Exécuter les migrations via Railway CLI ou interface

## API Documentation

### Endpoints Principaux

#### 1. Effectuer un Achat
```http
POST /api/woyofal/acheter
Content-Type: application/json

{
  "numeroCompteur": "12345678",
  "montant": 5000,
  "localisation": "Dakar"
}
```

**Réponse Succès :**
```json
{
  "data": {
    "compteur": "12345678",
    "reference": "WYF20241227000001",
    "code": "12345678901234567890",
    "date": "2024-12-27 10:30:00",
    "tranche": 2,
    "prix": 115.5,
    "nbreKwt": 43.25,
    "client": "Papa Sidy Fall"
  },
  "statut": "success",
  "code": 200,
  "message": "Achat effectué avec succès"
}
```

#### 2. Vérifier un Compteur
```http
GET /api/woyofal/compteur/{numero}
```

#### 3. Obtenir les Tranches de Prix
```http
GET /api/woyofal/tranches
```

#### 4. Historique des Achats
```http
GET /api/woyofal/historique/{numero?}?limit=50
```

#### 5. Health Check
```http
GET /api/woyofal/health
```

### Système de Tranches

Le système utilise des tranches progressives :

| Tranche | kWh Min | kWh Max | Prix/kWh (FCFA) |
|---------|---------|---------|-----------------|
| 1       | 0       | 150     | 96.52          |
| 2       | 150     | 250     | 115.5          |
| 3       | 250+    | ∞       | 123.75         |

## Intégration avec MaxITSA

### Configuration

1. **URL de base** : `https://votre-domaine.railway.app/api/woyofal`

2. **Headers requis** :
```http
Content-Type: application/json
Accept: application/json
```

3. **Authentification** : Aucune (pour l'instant)

### Exemple d'Intégration PHP

```php
<?php

class WoyofalClient
{
    private string $baseUrl;
    
    public function __construct(string $baseUrl)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
    }
    
    public function acheterCredit(string $numeroCompteur, float $montant): array
    {
        $url = $this->baseUrl . '/api/woyofal/acheter';
        
        $data = [
            'numeroCompteur' => $numeroCompteur,
            'montant' => $montant,
            'localisation' => 'MaxITSA'
        ];
        
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            CURLOPT_TIMEOUT => 30
        ]);
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        if ($httpCode !== 200) {
            throw new Exception("Erreur API Woyofal: HTTP $httpCode");
        }
        
        return json_decode($response, true);
    }
    
    public function verifierCompteur(string $numero): array
    {
        $url = $this->baseUrl . '/api/woyofal/compteur/' . $numero;
        
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30
        ]);
        
        $response = curl_exec($curl);
        curl_close($curl);
        
        return json_decode($response, true);
    }
}

// Utilisation
$client = new WoyofalClient('https://appwoyofal.railway.app');

try {
    // Vérifier le compteur avant l'achat
    $compteur = $client->verifierCompteur('12345678');
    
    if ($compteur['statut'] === 'success') {
        // Effectuer l'achat
        $achat = $client->acheterCredit('12345678', 5000);
        
        if ($achat['statut'] === 'success') {
            echo "Code de recharge: " . $achat['data']['code'];
        }
    }
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage();
}
```

### Gestion des Erreurs

| Code | Message | Description |
|------|---------|-------------|
| 400  | Données invalides | Paramètres manquants ou incorrects |
| 404  | Compteur non trouvé | Numéro de compteur inexistant |
| 500  | Erreur serveur | Problème technique interne |

### Exemple de Réponse d'Erreur

```json
{
  "data": null,
  "statut": "error",
  "code": 404,
  "message": "Le numéro de compteur non retrouvé"
}
```

## Tests

### Tests avec HTTPClient (VSCode)

Créer un fichier `tests.http` :

```http
### Test Health Check
GET http://localhost:8080/api/woyofal/health

### Test Verification Compteur
GET http://localhost:8080/api/woyofal/compteur/12345678

### Test Achat
POST http://localhost:8080/api/woyofal/acheter
Content-Type: application/json

{
  "numeroCompteur": "12345678",
  "montant": 5000
}

### Test Tranches
GET http://localhost:8080/api/woyofal/tranches
```

## Monitoring et Logs

### Journalisation

Toutes les transactions sont automatiquement journalisées avec :
- Date et heure
- Adresse IP
- Localisation
- Statut (SUCCESS/ÉCHEC)
- Détails de la transaction

### Endpoints de Monitoring

```http
GET /api/woyofal/statistiques?dateDebut=2024-01-01&dateFin=2024-12-31
GET /api/woyofal/journal?page=1&limite=50
```

## Sécurité

### Recommandations

1. **HTTPS Obligatoire** en production
2. **Rate Limiting** pour prévenir les abus
3. **Validation stricte** des données d'entrée
4. **Logs de sécurité** pour le monitoring
5. **Authentification API** (à implémenter selon les besoins)

### Variables Sensibles

Utiliser des variables d'environnement pour :
- Mots de passe de base de données
- Clés API
- Secrets de chiffrement

## Support

### Logs d'Application

Les logs sont disponibles dans :
- Docker : `docker-compose logs appwoyofal`
- Railway : Interface de monitoring Railway

### Debugging

Pour activer le mode debug en local :
```bash
# Dans .env
APP_DEBUG=true
LOG_LEVEL=debug
```

### Contact

- Email : dev@woyofal.sn
- Documentation complète : `/docs/api`
- Support technique : Voir les logs d'application

## Changelog

### Version 1.0.0
- Implémentation complète de l'API
- Système de tranches progressives
- Journalisation complète
- Support PostgreSQL
- Déploiement Docker/Railway
- Respect des principes SOLID
- Pattern Singleton optimisé
