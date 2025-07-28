-- Migration: Création de la table compteurs
-- Date: 2025-01-27
-- Description: Table pour stocker les informations des compteurs électriques

CREATE TABLE IF NOT EXISTS compteurs (
    id SERIAL PRIMARY KEY,
    numero VARCHAR(50) UNIQUE NOT NULL,
    client_id INTEGER NOT NULL,
    statut VARCHAR(20) DEFAULT 'ACTIF' CHECK (statut IN ('ACTIF', 'INACTIF', 'SUSPENDU')),
    solde_actuel DECIMAL(10,2) DEFAULT 0.00,
    dernier_achat TIMESTAMP NULL,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_compteur_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE RESTRICT
);

-- Index pour optimiser les recherches
CREATE UNIQUE INDEX IF NOT EXISTS idx_compteurs_numero ON compteurs(numero);
CREATE INDEX IF NOT EXISTS idx_compteurs_client_id ON compteurs(client_id);
CREATE INDEX IF NOT EXISTS idx_compteurs_statut ON compteurs(statut);

-- Commentaires sur les colonnes
COMMENT ON TABLE compteurs IS 'Table des compteurs électriques';
COMMENT ON COLUMN compteurs.id IS 'Identifiant unique du compteur';
COMMENT ON COLUMN compteurs.numero IS 'Numéro unique du compteur';
COMMENT ON COLUMN compteurs.client_id IS 'Référence vers le client propriétaire';
COMMENT ON COLUMN compteurs.statut IS 'Statut du compteur (ACTIF, INACTIF, SUSPENDU)';
COMMENT ON COLUMN compteurs.solde_actuel IS 'Solde actuel en kWh du compteur';
COMMENT ON COLUMN compteurs.dernier_achat IS 'Date du dernier achat effectué';
COMMENT ON COLUMN compteurs.date_creation IS 'Date de création du compteur';
COMMENT ON COLUMN compteurs.date_modification IS 'Date de dernière modification';
