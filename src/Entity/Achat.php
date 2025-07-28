<?php

namespace AppWoyofal\Entity;

use DevNoKage\SetterGetterTrait;

class Achat
{
    use SetterGetterTrait;
    
    private int $id;
    private string $reference;
    private string $codeRecharge;
    private string $numeroCompteur;
    private float $montant;
    private float $nbreKwt;
    private int $trancheNumero;
    private float $prixUnitaire;
    private string $nomClient;
    private string $adresseIp;
    private string $localisation;
    private string $statut; // SUCCESS, ECHEC
    private \DateTime $dateAchat;
    private \DateTime $heureAchat;

    public function __construct()
    {
        $this->dateAchat = new \DateTime();
        $this->heureAchat = new \DateTime();
        $this->reference = $this->genererReference();
        $this->codeRecharge = $this->genererCodeRecharge();
        $this->statut = 'SUCCESS';
    }

    private function genererReference(): string
    {
        return 'WYF' . date('Ymd') . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
    }

    private function genererCodeRecharge(): string
    {
        // Génère un code de 20 chiffres pour la recharge
        $code = '';
        for ($i = 0; $i < 20; $i++) {
            $code .= rand(0, 9);
        }
        return $code;
    }

    public function calculerKwt(float $montant, array $tranches): void
    {
        $montantRestant = $montant;
        $kwtTotal = 0;
        $trancheAppliquee = null;

        // Trier les tranches par numéro
        usort($tranches, function($a, $b) {
            return $a->getNumero() <=> $b->getNumero();
        });

        foreach ($tranches as $tranche) {
            if ($montantRestant <= 0) break;

            $seuilMax = $tranche->getSeuilMax();
            $prixUnitaire = $tranche->getPrixUnitaire();
            
            if ($seuilMax > 0) {
                $kwtMaxTranche = $seuilMax - $tranche->getSeuilMin();
                $montantMaxTranche = $kwtMaxTranche * $prixUnitaire;
                
                if ($montantRestant >= $montantMaxTranche) {
                    $kwtTotal += $kwtMaxTranche;
                    $montantRestant -= $montantMaxTranche;
                } else {
                    $kwtTranche = $montantRestant / $prixUnitaire;
                    $kwtTotal += $kwtTranche;
                    $montantRestant = 0;
                    $trancheAppliquee = $tranche;
                }
            } else {
                // Dernière tranche sans limite
                $kwtTranche = $montantRestant / $prixUnitaire;
                $kwtTotal += $kwtTranche;
                $montantRestant = 0;
                $trancheAppliquee = $tranche;
            }
        }

        $this->nbreKwt = round($kwtTotal, 2);
        $this->montant = $montant;
        
        if ($trancheAppliquee) {
            $this->trancheNumero = $trancheAppliquee->getNumero();
            $this->prixUnitaire = $trancheAppliquee->getPrixUnitaire();
        }
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id ?? null,
            'reference' => $this->reference ?? null,
            'codeRecharge' => $this->codeRecharge ?? null,
            'numeroCompteur' => $this->numeroCompteur ?? null,
            'montant' => $this->montant ?? null,
            'nbreKwt' => $this->nbreKwt ?? null,
            'trancheNumero' => $this->trancheNumero ?? null,
            'prixUnitaire' => $this->prixUnitaire ?? null,
            'nomClient' => $this->nomClient ?? null,
            'adresseIp' => $this->adresseIp ?? null,
            'localisation' => $this->localisation ?? null,
            'statut' => $this->statut ?? null,
            'dateAchat' => $this->dateAchat->format('Y-m-d'),
            'heureAchat' => $this->heureAchat->format('H:i:s'),
            'dateHeure' => $this->dateAchat->format('Y-m-d H:i:s')
        ];
    }

    public function fromArray(array $data): self
    {
        if (isset($data['id'])) $this->id = (int)$data['id'];
        if (isset($data['reference'])) $this->reference = $data['reference'];
        if (isset($data['code_recharge'])) $this->codeRecharge = $data['code_recharge'];
        if (isset($data['numero_compteur'])) $this->numeroCompteur = $data['numero_compteur'];
        if (isset($data['montant'])) $this->montant = (float)$data['montant'];
        if (isset($data['nbre_kwt'])) $this->nbreKwt = (float)$data['nbre_kwt'];
        if (isset($data['tranche_numero'])) $this->trancheNumero = (int)$data['tranche_numero'];
        if (isset($data['prix_unitaire'])) $this->prixUnitaire = (float)$data['prix_unitaire'];
        if (isset($data['nom_client'])) $this->nomClient = $data['nom_client'];
        if (isset($data['adresse_ip'])) $this->adresseIp = $data['adresse_ip'];
        if (isset($data['localisation'])) $this->localisation = $data['localisation'];
        if (isset($data['statut'])) $this->statut = $data['statut'];
        if (isset($data['date_achat'])) {
            $this->dateAchat = new \DateTime($data['date_achat']);
        }
        if (isset($data['heure_achat'])) {
            $this->heureAchat = new \DateTime($data['heure_achat']);
        }
        
        return $this;
    }

    public function marquerEchec(string $raison = ''): void
    {
        $this->statut = 'ECHEC';
        if ($raison) {
            $this->localisation = $raison; // Utiliser le champ localisation pour stocker la raison de l'échec
        }
    }
}
