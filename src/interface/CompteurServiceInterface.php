<?php

namespace AppWoyofal\Interface;

use AppWoyofal\Entity\Compteur;

interface CompteurServiceInterface
{
    public function verifierExistenceCompteur(string $numero): ?Compteur;
    public function obtenirCompteurAvecClient(string $numero): ?Compteur;
    public function mettreAJourSolde(string $numero, float $nouveauSolde): bool;
    public function changerStatutCompteur(string $numero, string $statut): bool;
    public function obtenirTousCompteurs(): array;
}
