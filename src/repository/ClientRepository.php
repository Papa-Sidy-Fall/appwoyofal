<?php

namespace AppWoyofal\Repository;

use DevNoKage\Singleton;
use DevNoKage\Database;
use AppWoyofal\Entity\Client;

class ClientRepository extends Singleton
{
    private Database $database;

    public function __construct()
    {
        $this->database = Database::getInstance();
    }

    public function creer(Client $client): bool
    {
        $sql = "INSERT INTO clients (nom, prenom, telephone, adresse, date_creation, date_modification) 
                VALUES (:nom, :prenom, :telephone, :adresse, :date_creation, :date_modification)";
        
        $stmt = $this->database->getConnexion()->prepare($sql);
        
        return $stmt->execute([
            'nom' => $client->getNom(),
            'prenom' => $client->getPrenom(),
            'telephone' => $client->getTelephone(),
            'adresse' => $client->getAdresse(),
            'date_creation' => $client->getDateCreation()->format('Y-m-d H:i:s'),
            'date_modification' => $client->getDateModification()->format('Y-m-d H:i:s')
        ]);
    }

    public function obtenirParId(int $id): ?Client
    {
        $sql = "SELECT * FROM clients WHERE id = :id";
        
        $stmt = $this->database->getConnexion()->prepare($sql);
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        $client = new Client();
        return $client->fromArray($data);
    }

    public function obtenirParTelephone(string $telephone): ?Client
    {
        $sql = "SELECT * FROM clients WHERE telephone = :telephone";
        
        $stmt = $this->database->getConnexion()->prepare($sql);
        $stmt->execute(['telephone' => $telephone]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        $client = new Client();
        return $client->fromArray($data);
    }

    public function obtenirTous(int $limit = 100, int $offset = 0): array
    {
        $sql = "SELECT * FROM clients ORDER BY date_creation DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->database->getConnexion()->prepare($sql);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll();

        $clients = [];
        foreach ($results as $data) {
            $client = new Client();
            $client->fromArray($data);
            $clients[] = $client;
        }

        return $clients;
    }

    public function mettreAJour(Client $client): bool
    {
        $sql = "UPDATE clients SET nom = :nom, prenom = :prenom, telephone = :telephone, 
                adresse = :adresse, date_modification = :date_modification 
                WHERE id = :id";
        
        $stmt = $this->database->getConnexion()->prepare($sql);
        
        return $stmt->execute([
            'nom' => $client->getNom(),
            'prenom' => $client->getPrenom(),
            'telephone' => $client->getTelephone(),
            'adresse' => $client->getAdresse(),
            'date_modification' => (new \DateTime())->format('Y-m-d H:i:s'),
            'id' => $client->getId()
        ]);
    }

    public function supprimer(int $id): bool
    {
        $sql = "DELETE FROM clients WHERE id = :id";
        
        $stmt = $this->database->getConnexion()->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    public function rechercherParNom(string $nom, string $prenom = ''): array
    {
        $sql = "SELECT * FROM clients WHERE nom ILIKE :nom";
        $params = ['nom' => "%$nom%"];

        if (!empty($prenom)) {
            $sql .= " AND prenom ILIKE :prenom";
            $params['prenom'] = "%$prenom%";
        }

        $sql .= " ORDER BY nom, prenom";

        $stmt = $this->database->getConnexion()->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll();

        $clients = [];
        foreach ($results as $data) {
            $client = new Client();
            $client->fromArray($data);
            $clients[] = $client;
        }

        return $clients;
    }
}
