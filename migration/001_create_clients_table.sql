-- Migration: Création de la table clients
-- Date: 2025-01-27
-- Description: Table pour stocker les informations des clients

CREATE TABLE IF NOT EXISTS clients (
    id SERIAL PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    telephone VARCHAR(20) UNIQUE NOT NULL,
    adresse TEXT,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Index pour optimiser les recherches
CREATE INDEX IF NOT EXISTS idx_clients_telephone ON clients(telephone);
CREATE INDEX IF NOT EXISTS idx_clients_nom_prenom ON clients(nom, prenom);

-- Commentaires sur les colonnes
COMMENT ON TABLE clients IS 'Table des clients Woyofal';
COMMENT ON COLUMN clients.id IS 'Identifiant unique du client';
COMMENT ON COLUMN clients.nom IS 'Nom de famille du client';
COMMENT ON COLUMN clients.prenom IS 'Prénom du client';
COMMENT ON COLUMN clients.telephone IS 'Numéro de téléphone unique du client';
COMMENT ON COLUMN clients.adresse IS 'Adresse du client';
COMMENT ON COLUMN clients.date_creation IS 'Date de création du compte client';
COMMENT ON COLUMN clients.date_modification IS 'Date de dernière modification';
