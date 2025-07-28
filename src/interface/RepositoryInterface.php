<?php

namespace AppWoyofal\Interface;

interface RepositoryInterface
{
    public function obtenirParId(int $id): ?object;
    public function obtenirTous(int $limit = 100, int $offset = 0): array;
    public function creer(object $entity): bool;
    public function mettreAJour(object $entity): bool;
}
