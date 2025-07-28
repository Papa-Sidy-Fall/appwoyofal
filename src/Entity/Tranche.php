<?php

namespace AppWoyofal\Entity;

use DevNoKage\SetterGetterTrait;

class Tranche
{
    use SetterGetterTrait;
    
    private int $id;
    private int $numero; // 1, 2, 3...
    private float $seuilMin; // kWh minimum
    private float $seuilMax; // kWh maximum
    private float $prixUnitaire; // Prix par kWh
    private string $description;
    private bool $actif;
    private \DateTime $dateCreation;
    private \DateTime $dateModification;

    public function __construct()
    {
        $this->dateCreation = new \DateTime();
        $this->dateModification = new \DateTime();
        $this->actif = true;
    }

    public function isApplicable(float $kwh): bool
    {
        return $this->actif && $kwh >= $this->seuilMin && ($this->seuilMax === 0 || $kwh <= $this->seuilMax);
    }

    public function calculerPrix(float $kwh): float
    {
        if (!$this->isApplicable($kwh)) {
            return 0.0;
        }
        
        $kwhTrancheMin = max($kwh - $this->seuilMin, 0);
        $kwhTrancheMax = $this->seuilMax > 0 ? min($kwh, $this->seuilMax) - $this->seuilMin : $kwhTrancheMin;
        
        return $kwhTrancheMax * $this->prixUnitaire;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id ?? null,
            'numero' => $this->numero ?? null,
            'seuilMin' => $this->seuilMin ?? null,
            'seuilMax' => $this->seuilMax ?? null,
            'prixUnitaire' => $this->prixUnitaire ?? null,
            'description' => $this->description ?? null,
            'actif' => $this->actif ?? true,
            'dateCreation' => $this->dateCreation->format('Y-m-d H:i:s'),
            'dateModification' => $this->dateModification->format('Y-m-d H:i:s')
        ];
    }

    public function fromArray(array $data): self
    {
        if (isset($data['id'])) $this->id = (int)$data['id'];
        if (isset($data['numero'])) $this->numero = (int)$data['numero'];
        if (isset($data['seuil_min'])) $this->seuilMin = (float)$data['seuil_min'];
        if (isset($data['seuil_max'])) $this->seuilMax = (float)$data['seuil_max'];
        if (isset($data['prix_unitaire'])) $this->prixUnitaire = (float)$data['prix_unitaire'];
        if (isset($data['description'])) $this->description = $data['description'];
        if (isset($data['actif'])) $this->actif = (bool)$data['actif'];
        if (isset($data['date_creation'])) {
            $this->dateCreation = new \DateTime($data['date_creation']);
        }
        if (isset($data['date_modification'])) {
            $this->dateModification = new \DateTime($data['date_modification']);
        }
        
        return $this;
    }

    public function updateTimestamp(): void
    {
        $this->dateModification = new \DateTime();
    }
}
