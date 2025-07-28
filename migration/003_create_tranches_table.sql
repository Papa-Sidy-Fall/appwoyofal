-- Migration: Création de la table tranches
-- Date: 2025-01-27
-- Description: Table pour stocker les tranches de prix du système de facturation

CREATE TABLE IF NOT EXISTS tranches (
    id SERIAL PRIMARY KEY,
    numero INTEGER NOT NULL UNIQUE,
    seuil_min DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    seuil_max DECIMAL(10,2) DEFAULT 0.00, -- 0 signifie pas de limite supérieure
    prix_unitaire DECIMAL(8,2) NOT NULL,
    description TEXT,
    actif BOOLEAN DEFAULT true,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT chk_seuil_coherent CHECK (seuil_max = 0 OR seuil_max > seuil_min),
    CONSTRAINT chk_prix_positif CHECK (prix_unitaire > 0)
);

-- Index pour optimiser les recherches
CREATE UNIQUE INDEX IF NOT EXISTS idx_tranches_numero ON tranches(numero);
CREATE INDEX IF NOT EXISTS idx_tranches_actif ON tranches(actif);
CREATE INDEX IF NOT EXISTS idx_tranches_seuils ON tranches(seuil_min, seuil_max);

-- Commentaires sur les colonnes
COMMENT ON TABLE tranches IS 'Table des tranches de prix pour la facturation électrique';
COMMENT ON COLUMN tranches.id IS 'Identifiant unique de la tranche';
COMMENT ON COLUMN tranches.numero IS 'Numéro d''ordre de la tranche (1, 2, 3...)';
COMMENT ON COLUMN tranches.seuil_min IS 'Seuil minimum en kWh pour cette tranche';
COMMENT ON COLUMN tranches.seuil_max IS 'Seuil maximum en kWh (0 = pas de limite)';
COMMENT ON COLUMN tranches.prix_unitaire IS 'Prix unitaire par kWh pour cette tranche';
COMMENT ON COLUMN tranches.description IS 'Description de la tranche';
COMMENT ON COLUMN tranches.actif IS 'Indique si la tranche est active';
COMMENT ON COLUMN tranches.date_creation IS 'Date de création de la tranche';
COMMENT ON COLUMN tranches.date_modification IS 'Date de dernière modification';
