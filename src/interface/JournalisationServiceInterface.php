<?php

namespace AppWoyofal\Interface;

use AppWoyofal\Entity\Achat;

interface JournalisationServiceInterface
{
    public function journaliserAchat(Achat $achat): bool;
    public function obtenirJournalPagine(int $page, int $limite, string $filtre = ''): array;
    public function obtenirStatistiques(string $dateDebut = '', string $dateFin = ''): array;
    public function supprimerAnciennesEntrees(int $jourConservation = 365): int;
}
