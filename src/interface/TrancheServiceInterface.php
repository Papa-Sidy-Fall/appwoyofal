<?php

namespace AppWoyofal\Interface;

use AppWoyofal\Entity\Tranche;

interface TrancheServiceInterface
{
    public function obtenirTranchesActives(): array;
    public function calculerRepartitionTranches(float $montant): array;
    public function calculerKwtPourMontant(float $montant): float;
    public function obtenirTrancheParNumero(int $numero): ?Tranche;
}
