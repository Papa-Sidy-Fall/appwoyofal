# Documentation d'Intégration - AppWoyofal

## 📖 Présentation

AppWoyofal est une API REST qui simule le système de prépaiement d'électricité de la Senelec. Elle permet l'achat de crédit Woyofal avec un système de tranches de prix progressif.

## 🏗️ Architecture

L'application suit les principes SOLID et utilise le pattern Singleton pour la gestion des services :

- **Controller** : [`WoyofalController`](src/Controller/WoyofalController.php) - Gestion des requêtes HTTP
- **Services** : Logique métier avec pattern Singleton
- **Entities** : Modèles de données
- **Interfaces** : Contrats pour les services
- **Repository Pattern** : Accès aux données

### Services Principaux

1. **AchatService** : Gestion des achats de crédit
2. **CompteurService** : Gestion des compteurs électriques
3. **TrancheService** : Calcul des tranches de prix
4. **JournalisationService** : Journalisation et statistiques

## 🚀 Déploiement

### Prérequis

- Docker & Docker Compose
- Accès à Railway (pour la base de données)
- Accès à Render (pour l'application)

### 1. Configuration de la Base de Données (Railway)

1. Créer un projet PostgreSQL sur Railway
2. Noter les informations de connexion :
   ```
   HOST: [RAILWAY_HOST]
   PORT: [RAILWAY_PORT]
   DATABASE: [DATABASE_NAME]
   USERNAME: [USERNAME]
   PASSWORD: [PASSWORD]
   ```

### 2. Variables d'Environnement

Créer un fichier `.env` dans le dossier `project-initializer` :

```bash
# Base de données
DB_HOST=[RAILWAY_HOST]
DB_PORT=[RAILWAY_PORT]
DB_NAME=[DATABASE_NAME]
DB_USER=[USERNAME]
DB_PASSWORD=[PASSWORD]

# Application
ENVIRONMENT=production
APP_DEBUG=false
LOG_LEVEL=info
```

### 3. Migrations et Seeders

#### Exécuter les migrations
```bash
cd project-initializer
php migration/run_migrations.php
```

#### Insérer les données de test
```bash
cd project-initializer
php seeders/seeder.php
```

### 4. Déploiement sur Render

#### Option A: Docker Hub (Recommandé)

1. **Build et Push de l'image** :
   ```bash
   # À la racine du projet
   docker build -t votre-username/appwoyofal:latest .
   docker push votre-username/appwoyofal:latest
   ```

2. **Configuration Render** :
   - Service Type: Web Service
   - Docker Image: `votre-username/appwoyofal:latest`
   - Port: 80
   - Variables d'environnement:
     ```
     DB_HOST=[RAILWAY_HOST]
     DB_PORT=[RAILWAY_PORT]
     DB_NAME=[DATABASE_NAME]
     DB_USER=[USERNAME]
     DB_PASSWORD=[PASSWORD]
     ENVIRONMENT=production
     ```

#### Option B: GitHub Deploy

1. Pusher le code sur GitHub
2. Connecter le repository à Render
3. Configuration Render :
   - Build Command: `docker build -t appwoyofal .`
   - Start Command: `docker run -p 80:80 appwoyofal`

### 5. Test Local avec Docker

```bash
# À la racine du projet
docker-compose up -d

# Vérifier que l'application fonctionne
curl http://localhost:8080/api/woyofal/health
```

## 📡 API Endpoints

### Base URL
- **Local** : `http://localhost:8080`
- **Production** : `https://votre-app.render.com`

### Endpoints Disponibles

#### 1. Effectuer un Achat
```http
POST /api/woyofal/acheter
Content-Type: application/json

{
    "numeroCompteur": "WYF001000001",
    "montant": 5000,
    "localisation": "Dakar"
}
```

**Réponse Success:**
```json
{
    "data": {
        "compteur": "WYF001000001",
        "reference": "WYF20250127123456",
        "code": "12345678901234567890",
        "date": "2025-01-27 14:30:00",
        "tranche": 2,
        "prix": 89.99,
        "nbreKwt": 55.56,
        "client": "Fall Papa Sidy"
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

#### 3. Obtenir les Tranches
```http
GET /api/woyofal/tranches
```

#### 4. Calculer le Prix
```http
POST /api/woyofal/calculer-prix
Content-Type: application/json

{
    "montant": 5000
}
```

#### 5. Historique des Achats
```http
GET /api/woyofal/historique/{numero?}?limit=50
```

#### 6. Obtenir un Achat par Référence
```http
GET /api/woyofal/achat/{reference}
```

#### 7. Statistiques
```http
GET /api/woyofal/statistiques?dateDebut=2025-01-01&dateFin=2025-01-31
```

#### 8. Journal des Achats
```http
GET /api/woyofal/journal?page=1&limite=50&filtre=search
```

#### 9. Health Check
```http
GET /api/woyofal/health
```

## 🔌 Intégration dans MaxITSA

### Étapes d'Intégration

1. **Configuration des URLs** :
   ```php
   define('WOYOFAL_API_URL', 'https://votre-app.render.com/api/woyofal');
   ```

2. **Service Client pour MaxITSA** :
   ```php
   class WoyofalClient 
   {
       private string $apiUrl;
       
       public function __construct(string $apiUrl) 
       {
           $this->apiUrl = $apiUrl;
       }
       
       public function acheterCredit(string $numeroCompteur, float $montant): array
       {
           $data = [
               'numeroCompteur' => $numeroCompteur,
               'montant' => $montant,
               'localisation' => 'MaxITSA'
           ];
           
           return $this->makeRequest('POST', '/acheter', $data);
       }
       
       public function verifierCompteur(string $numero): array
       {
           return $this->makeRequest('GET', "/compteur/{$numero}");
       }
       
       private function makeRequest(string $method, string $endpoint, array $data = []): array
       {
           $ch = curl_init();
           curl_setopt_array($ch, [
               CURLOPT_URL => $this->apiUrl . $endpoint,
               CURLOPT_RETURNTRANSFER => true,
               CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
               CURLOPT_CUSTOMREQUEST => $method
           ]);
           
           if (!empty($data)) {
               curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
           }
           
           $response = curl_exec($ch);
           curl_close($ch);
           
           return json_decode($response, true);
       }
   }
   ```

3. **Utilisation dans MaxITSA** :
   ```php
   $woyofal = new WoyofalClient(WOYOFAL_API_URL);
   
   // Vérifier l'existence du compteur
   $compteur = $woyofal->verifierCompteur($numeroCompteur);
   if ($compteur['statut'] !== 'success') {
       throw new Exception('Compteur non trouvé');
   }
   
   // Vérifier le solde du compte principal
   if ($comptePrincipal->getSolde() < $montant) {
       throw new Exception('Solde insuffisant');
   }
   
   // Effectuer l'achat
   $achat = $woyofal->acheterCredit($numeroCompteur, $montant);
   if ($achat['statut'] === 'success') {
       // Débiter le compte principal
       $comptePrincipal->debiter($montant);
       
       // Générer le reçu
       $recu = $this->genererRecu($achat['data']);
       
       return $recu;
   }
   ```

### Génération de Reçu

```php
public function genererRecu(array $achatData): array
{
    return [
        'nom_client' => $achatData['client'],
        'numero_compteur' => $achatData['compteur'],
        'code_recharge' => $achatData['code'],
        'kwh' => $achatData['nbreKwt'],
        'date_heure' => $achatData['date'],
        'tranche' => $achatData['tranche'],
        'prix_unitaire' => $achatData['prix'],
        'reference' => $achatData['reference']
    ];
}
```

## 🔧 Système de Tranches

### Configuration par Défaut

| Tranche | Seuil Min (kWh) | Seuil Max (kWh) | Prix Unitaire (F CFA) | Description |
|---------|-----------------|-----------------|----------------------|-------------|
| 1       | 0               | 150             | 79.99                | Tarif social |
| 2       | 150             | 250             | 89.99                | Tarif normal |
| 3       | 250             | ∞               | 99.99                | Tarif supérieur |

### Exemple de Calcul

**Montant**: 10 000 F CFA

1. **Tranche 1** (0-150 kWh): 150 × 79.99 = 11 998.5 F CFA → 1.88 kWh utilisés (1 503.81 F CFA)
2. **Tranche 2** (150-250 kWh): Montant restant 8 496.19 F CFA ÷ 89.99 = 94.40 kWh
3. **Total kWh**: 1.88 + 94.40 = 96.28 kWh

## 📊 Journalisation

Toutes les transactions sont automatiquement journalisées :

- **Base de données** : Table `journal_achats`
- **Fichiers logs** : `logs/woyofal_YYYY-MM-DD.log`

### Format du Log
```
[2025-01-27 14:30:00] SUCCESS | REF: WYF20250127123456 | COMPTEUR: WYF001000001 | MONTANT: 5000.00 | KWT: 55.56 | STATUT: SUCCESS | IP: 192.168.1.1 | LOC: Dakar | UA: PostmanRuntime/7.26.8
```

## 🛠️ Maintenance

### Commandes Utiles

1. **Nettoyer les anciens logs** :
   ```php
   $journalisation = JournalisationService::getInstance();
   $supprimees = $journalisation->nettoyerAncienLogs(90); // 90 jours
   ```

2. **Vérifier la santé du service** :
   ```bash
   curl https://votre-app.render.com/api/woyofal/health
   ```

3. **Réinitialiser les tranches** :
   ```php
   $trancheService = TrancheService::getInstance();
   $trancheService->initialiserTranchesParDefaut();
   ```

### Monitoring

- **Health Check** : `/api/woyofal/health`
- **Logs** : Consultable via l'interface Render
- **Base de données** : Monitoring via Railway Dashboard

## 🔒 Sécurité

1. **Variables d'environnement** : Secrets stockés de façon sécurisée
2. **Validation** : Validation stricte des entrées
3. **Logging** : Traçabilité complète des transactions
4. **HTTPS** : Communications chiffrées en production

## 📞 Support

En cas de problème :

1. Vérifier les logs de l'application
2. Tester le health check
3. Vérifier la connectivité à la base de données
4. Consulter la documentation des APIs

## 🔄 Mise à Jour

Pour mettre à jour l'application :

1. Modifier le code
2. Rebuild l'image Docker
3. Push sur Docker Hub
4. Redéployer sur Render

```bash
docker build -t votre-username/appwoyofal:v1.1.0 .
docker push votre-username/appwoyofal:v1.1.0
```

## 📈 Évolutions Futures

- Interface d'administration
- API de gestion des tranches
- Système de notifications
- Dashboard analytique
- API de rapports avancés
