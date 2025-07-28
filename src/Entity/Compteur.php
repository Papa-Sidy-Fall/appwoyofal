<?php

namespace AppWoyofal\Entity;

use DevNoKage\SetterGetterTrait;

class Compteur
{
    use SetterGetterTrait;
    
    private int $id;
    private string $numero;
    private int $clientId;
    private string $statut; // ACTIF, INACTIF, SUSPENDU
    private float $soldeActuel;
    private \DateTime $dernierAchat;
    private \DateTime $dateCreation;
    private \DateTime $dateModification;
    private ?Client $client = null;

    public function __construct()
    {
        $this->dateCreation = new \DateTime();
        $this->dateModification = new \DateTime();
        $this->soldeActuel = 0.0;
        $this->statut = 'ACTIF';
    }

    public function isActif(): bool
    {
        return $this->statut === 'ACTIF';
    }

    public function crediter(float $montant): void
    {
        $this->soldeActuel += $montant;
        $this->updateTimestamp();
    }

    public function debiter(float $montant): bool
    {
        if ($this->soldeActuel >= $montant) {
            $this->soldeActuel -= $montant;
            $this->updateTimestamp();
            return true;
        }
        return false;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id ?? null,
            'numero' => $this->numero ?? null,
            'clientId' => $this->clientId ?? null,
            'statut' => $this->statut ?? null,
            'soldeActuel' => $this->soldeActuel ?? 0.0,
            'dernierAchat' => $this->dernierAchat?->format('Y-m-d H:i:s'),
            'dateCreation' => $this->dateCreation->format('Y-m-d H:i:s'),
            'dateModification' => $this->dateModification->format('Y-m-d H:i:s'),
            'client' => $this->client?->toArray()
        ];
    }

    public function fromArray(array $data): self
    {
        if (isset($data['id'])) $this->id = (int)$data['id'];
        if (isset($data['numero'])) $this->numero = $data['numero'];
        if (isset($data['client_id'])) $this->clientId = (int)$data['client_id'];
        if (isset($data['statut'])) $this->statut = $data['statut'];
        if (isset($data['solde_actuel'])) $this->soldeActuel = (float)$data['solde_actuel'];
        if (isset($data['dernier_achat'])) {
            $this->dernierAchat = new \DateTime($data['dernier_achat']);
        }
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

    public function setClient(Client $client): void
    {
        $this->client = $client;
    }
}
