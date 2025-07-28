<?php

namespace AppWoyofal\Service;

use DevNoKage\Singleton;
use DevNoKage\Database;
use AppWoyofal\Entity\Compteur;
use AppWoyofal\Entity\Client;
use AppWoyofal\Interface\CompteurServiceInterface;

class CompteurService extends Singleton implements CompteurServiceInterface
{
    private Database $database;

    public function __construct()
    {
        $this->database = Database::getInstance();
    }

    public function verifierExistenceCompteur(string $numeroCompteur): ?Compteur
    {
        $sql = "SELECT c.*, cl.nom, cl.prenom, cl.telephone, cl.adresse, 
                       cl.date_creation as client_date_creation, 
                       cl.date_modification as client_date_modification
                FROM compteurs c 
                INNER JOIN clients cl ON c.client_id = cl.id 
                WHERE c.numero = :numero AND c.statut = 'ACTIF'";
        
        $stmt = $this->database->getConnexion()->prepare($sql);
        $stmt->execute(['numero' => $numeroCompteur]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        $compteur = new Compteur();
        $compteur->fromArray($data);

        // Créer et associer le client
        $client = new Client();
        $clientData = [
            'id' => $data['client_id'],
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'telephone' => $data['telephone'],
            'adresse' => $data['adresse'],
            'date_creation' => $data['client_date_creation'],
            'date_modification' => $data['client_date_modification']
        ];
        $client->fromArray($clientData);
        $compteur->setClient($client);

        return $compteur;
    }

    public function obtenirTousCompteurs(): array
    {
        $sql = "SELECT c.*, cl.nom, cl.prenom, cl.telephone, cl.adresse 
                FROM compteurs c 
                INNER JOIN clients cl ON c.client_id = cl.id 
                ORDER BY c.date_creation DESC";
        
        $stmt = $this->database->getConnexion()->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll();

        $compteurs = [];
        foreach ($results as $data) {
            $compteur = new Compteur();
            $compteur->fromArray($data);

            $client = new Client();
            $client->fromArray([
                'id' => $data['client_id'],
                'nom' => $data['nom'],
                'prenom' => $data['prenom'],
                'telephone' => $data['telephone'],
                'adresse' => $data['adresse']
            ]);
            $compteur->setClient($client);

            $compteurs[] = $compteur;
        }

        return $compteurs;
    }

    public function creerCompteur(Compteur $compteur): bool
    {
        $sql = "INSERT INTO compteurs (numero, client_id, statut, solde_actuel, date_creation, date_modification) 
                VALUES (:numero, :client_id, :statut, :solde_actuel, :date_creation, :date_modification)";
        
        $stmt = $this->database->getConnexion()->prepare($sql);
        
        return $stmt->execute([
            'numero' => $compteur->getNumero(),
            'client_id' => $compteur->getClientId(),
            'statut' => $compteur->getStatut(),
            'solde_actuel' => $compteur->getSoldeActuel(),
            'date_creation' => $compteur->getDateCreation()->format('Y-m-d H:i:s'),
            'date_modification' => $compteur->getDateModification()->format('Y-m-d H:i:s')
        ]);
    }

    public function mettreAJourSolde(string $numeroCompteur, float $nouveauSolde): bool
    {
        $sql = "UPDATE compteurs SET solde_actuel = :solde, dernier_achat = :dernier_achat, 
                date_modification = :date_modification WHERE numero = :numero";
        
        $stmt = $this->database->getConnexion()->prepare($sql);
        
        return $stmt->execute([
            'solde' => $nouveauSolde,
            'dernier_achat' => (new \DateTime())->format('Y-m-d H:i:s'),
            'date_modification' => (new \DateTime())->format('Y-m-d H:i:s'),
            'numero' => $numeroCompteur
        ]);
    }

    public function obtenirCompteurParId(int $id): ?Compteur
    {
        $sql = "SELECT c.*, cl.nom, cl.prenom, cl.telephone, cl.adresse 
                FROM compteurs c 
                INNER JOIN clients cl ON c.client_id = cl.id 
                WHERE c.id = :id";
        
        $stmt = $this->database->getConnexion()->prepare($sql);
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        $compteur = new Compteur();
        $compteur->fromArray($data);

        $client = new Client();
        $client->fromArray([
            'id' => $data['client_id'],
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'telephone' => $data['telephone'],
            'adresse' => $data['adresse']
        ]);
        $compteur->setClient($client);

        return $compteur;
    }

    public function obtenirCompteurAvecClient(string $numero): ?Compteur
    {
        return $this->verifierExistenceCompteur($numero);
    }

    public function changerStatutCompteur(string $numero, string $statut): bool
    {
        $sql = "UPDATE compteurs SET statut = :statut, date_modification = :date_modification 
                WHERE numero = :numero";
        
        $stmt = $this->database->getConnexion()->prepare($sql);
        
        return $stmt->execute([
            'statut' => $statut,
            'date_modification' => (new \DateTime())->format('Y-m-d H:i:s'),
            'numero' => $numero
        ]);
    }
}
