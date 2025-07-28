<?php

/**
 * Fichier des fonctions helper pour l'application AppWoyofal
 */

/**
 * Formater un numéro de téléphone sénégalais
 */
function formatTelephoneSenegal(string $telephone): string
{
    // Supprimer tous les espaces et caractères spéciaux
    $telephone = preg_replace('/[^0-9]/', '', $telephone);
    
    // Ajouter l'indicatif pays si nécessaire
    if (strlen($telephone) === 9 && (str_starts_with($telephone, '7') || str_starts_with($telephone, '3'))) {
        $telephone = '221' . $telephone;
    }
    
    // Format: +221 XX XXX XX XX
    if (strlen($telephone) === 12 && str_starts_with($telephone, '221')) {
        return '+' . substr($telephone, 0, 3) . ' ' . 
               substr($telephone, 3, 2) . ' ' . 
               substr($telephone, 5, 3) . ' ' . 
               substr($telephone, 8, 2) . ' ' . 
               substr($telephone, 10, 2);
    }
    
    return $telephone;
}

/**
 * Valider un numéro de téléphone sénégalais
 */
function validerTelephoneSenegal(string $telephone): bool
{
    $telephone = preg_replace('/[^0-9]/', '', $telephone);
    
    // Vérifier les formats valides
    if (strlen($telephone) === 9) {
        return preg_match('/^[37][0-9]{8}$/', $telephone);
    }
    
    if (strlen($telephone) === 12) {
        return preg_match('/^221[37][0-9]{8}$/', $telephone);
    }
    
    return false;
}

/**
 * Générer un numéro de compteur unique
 */
function genererNumeroCompteur(): string
{
    $prefix = 'WYF';
    $timestamp = time();
    $random = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    return $prefix . substr($timestamp, -6) . $random;
}

/**
 * Valider un numéro de compteur
 */
function validerNumeroCompteur(string $numero): bool
{
    return preg_match('/^WYF[0-9]{10}$/', $numero);
}

/**
 * Formater un montant en FCFA
 */
function formaterMontantFCFA(float $montant): string
{
    return number_format($montant, 0, ',', ' ') . ' FCFA';
}

/**
 * Formater les kWh
 */
function formaterKwh(float $kwh): string
{
    return number_format($kwh, 2, ',', ' ') . ' kWh';
}

/**
 * Calculer la différence en jours entre deux dates
 */
function calculerDifferenceJours(DateTime $date1, DateTime $date2): int
{
    $diff = $date1->diff($date2);
    return abs($diff->days);
}

/**
 * Vérifier si c'est un nouveau mois (remise à zéro des tranches)
 */
function estNouveauMois(DateTime $dernierAchat = null): bool
{
    if ($dernierAchat === null) {
        return true;
    }
    
    $maintenant = new DateTime();
    return $dernierAchat->format('Y-m') !== $maintenant->format('Y-m');
}

/**
 * Générer un hash sécurisé pour l'API
 */
function genererHashAPI(string $data, string $secret): string
{
    return hash_hmac('sha256', $data, $secret);
}

/**
 * Valider un hash API
 */
function validerHashAPI(string $data, string $hash, string $secret): bool
{
    return hash_equals($hash, genererHashAPI($data, $secret));
}

/**
 * Logger une action dans les fichiers de log
 */
function loggerAction(string $action, array $data = [], string $niveau = 'INFO'): void
{
    $logDir = __DIR__ . '/../../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logFile = $logDir . '/app_' . date('Y-m-d') . '.log';
    
    $logEntry = sprintf(
        "[%s] %s: %s | Data: %s" . PHP_EOL,
        date('Y-m-d H:i:s'),
        $niveau,
        $action,
        json_encode($data, JSON_UNESCAPED_UNICODE)
    );
    
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

/**
 * Nettoyer et valider les données d'entrée
 */
function nettoyerDonnees(array $data): array
{
    $donneesPropres = [];
    
    foreach ($data as $key => $value) {
        if (is_string($value)) {
            $donneesPropres[$key] = trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
        } elseif (is_numeric($value)) {
            $donneesPropres[$key] = $value;
        } elseif (is_array($value)) {
            $donneesPropres[$key] = nettoyerDonnees($value);
        } else {
            $donneesPropres[$key] = $value;
        }
    }
    
    return $donneesPropres;
}

/**
 * Obtenir l'adresse IP réelle du client
 */
function obtenirAdresseIP(): string
{
    $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
    
    foreach ($ipKeys as $key) {
        if (!empty($_SERVER[$key])) {
            $ips = explode(',', $_SERVER[$key]);
            $ip = trim($ips[0]);
            
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

/**
 * Formater une date en français
 */
function formaterDateFrancais(DateTime $date): string
{
    $mois = [
        1 => 'janvier', 2 => 'février', 3 => 'mars', 4 => 'avril',
        5 => 'mai', 6 => 'juin', 7 => 'juillet', 8 => 'août',
        9 => 'septembre', 10 => 'octobre', 11 => 'novembre', 12 => 'décembre'
    ];
    
    $jours = [
        'Monday' => 'lundi', 'Tuesday' => 'mardi', 'Wednesday' => 'mercredi',
        'Thursday' => 'jeudi', 'Friday' => 'vendredi', 'Saturday' => 'samedi', 'Sunday' => 'dimanche'
    ];
    
    $jourSemaine = $jours[$date->format('l')];
    $jour = $date->format('j');
    $moisNom = $mois[(int)$date->format('n')];
    $annee = $date->format('Y');
    
    return sprintf('%s %d %s %d', $jourSemaine, $jour, $moisNom, $annee);
}

/**
 * Convertir un tableau associatif en objet JSON
 */
function arrayToJsonObject(array $data): string
{
    return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

/**
 * Vérifier si l'environnement est en mode debug
 */
function estModeDebug(): bool
{
    return defined('APP_DEBUG') && APP_DEBUG === true;
}

/**
 * Générer un UUID v4
 */
function genererUUID(): string
{
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

/**
 * Valider un email
 */
function validerEmail(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Tronquer un texte avec ellipse
 */
function tronquerTexte(string $texte, int $longueur = 100, string $suffixe = '...'): string
{
    if (strlen($texte) <= $longueur) {
        return $texte;
    }
    
    return substr($texte, 0, $longueur - strlen($suffixe)) . $suffixe;
}
