<?php

namespace AppWoyofal\Entity;

use DevNoKage\SetterGetterTrait;

class Client
{
    use SetterGetterTrait;
    
    private int $id;
    private string $nom;
    private string $prenom;
    private string $telephone;
    private string $adresse;
    private \DateTime $dateCreation;
    private \DateTime $dateModification;

    public function __construct()
    {
        $this->dateCreation = new \DateTime();
        $this->dateModification = new \DateTime();
    }

    public function getNomComplet(): string
    {
        return $this->prenom . ' ' . $this->nom;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id ?? null,
            'nom' => $this->nom ?? null,
            'prenom' => $this->prenom ?? null,
            'telephone' => $this->telephone ?? null,
            'adresse' => $this->adresse ?? null,
            'nomComplet' => $this->getNomComplet(),
            'dateCreation' => $this->dateCreation->format('Y-m-d H:i:s'),
            'dateModification' => $this->dateModification->format('Y-m-d H:i:s')
        ];
    }

    public function fromArray(array $data): self
    {
        if (isset($data['id'])) $this->id = (int)$data['id'];
        if (isset($data['nom'])) $this->nom = $data['nom'];
        if (isset($data['prenom'])) $this->prenom = $data['prenom'];
        if (isset($data['telephone'])) $this->telephone = $data['telephone'];
        if (isset($data['adresse'])) $this->adresse = $data['adresse'];
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
