<?php

namespace AppWoyofal\Interface;

use AppWoyofal\Entity\Achat;

interface AchatServiceInterface
{
    public function effectuerAchat(string $numeroCompteur, float $montant, string $adresseIp = '', string $localisation = ''): array;
    public function obtenirHistoriqueAchats(string $numeroCompteur = '', int $limit = 50): array;
    public function obtenirAchatParReference(string $reference): ?Achat;
}
