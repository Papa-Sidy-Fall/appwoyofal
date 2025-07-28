-- Migration: Création de la table achats
-- Date: 2025-01-27
-- Description: Table pour stocker les achats de crédit Woyofal

CREATE TABLE IF NOT EXISTS achats (
    id SERIAL PRIMARY KEY,
    reference VARCHAR(50) UNIQUE NOT NULL,
    code_recharge VARCHAR(20) NOT NULL,
    numero_compteur VARCHAR(50) NOT NULL,
    montant DECIMAL(10,2) NOT NULL,
    nbre_kwt DECIMAL(10,3) NOT NULL,
    tranche_numero INTEGER,
    prix_unitaire DECIMAL(8,2),
    nom_client VARCHAR(200),
    adresse_ip INET,
    localisation VARCHAR(100),
    statut VARCHAR(20) DEFAULT 'SUCCESS' CHECK (statut IN ('SUCCESS', 'ECHEC')),
    date_achat DATE DEFAULT CURRENT_DATE,
    heure_achat TIME DEFAULT CURRENT_TIME,
    
    CONSTRAINT fk_achat_compteur FOREIGN KEY (numero_compteur) REFERENCES compteurs(numero),
    CONSTRAINT fk_achat_tranche FOREIGN KEY (tranche_numero) REFERENCES tranches(numero),
    CONSTRAINT chk_montant_positif CHECK (montant > 0),
    CONSTRAINT chk_kwt_positif CHECK (nbre_kwt >= 0)
);

-- Index pour optimiser les recherches
CREATE UNIQUE INDEX IF NOT EXISTS idx_achats_reference ON achats(reference);
CREATE INDEX IF NOT EXISTS idx_achats_compteur ON achats(numero_compteur);
CREATE INDEX IF NOT EXISTS idx_achats_date ON achats(date_achat, heure_achat);
CREATE INDEX IF NOT EXISTS idx_achats_statut ON achats(statut);
CREATE INDEX IF NOT EXISTS idx_achats_tranche ON achats(tranche_numero);

-- Commentaires sur les colonnes
COMMENT ON TABLE achats IS 'Table des achats de crédit Woyofal';
COMMENT ON COLUMN achats.id IS 'Identifiant unique de l''achat';
COMMENT ON COLUMN achats.reference IS 'Référence unique de l''achat';
COMMENT ON COLUMN achats.code_recharge IS 'Code de recharge généré pour le client';
COMMENT ON COLUMN achats.numero_compteur IS 'Numéro du compteur concerné';
COMMENT ON COLUMN achats.montant IS 'Montant payé par le client';
COMMENT ON COLUMN achats.nbre_kwt IS 'Nombre de kWh acheté';
COMMENT ON COLUMN achats.tranche_numero IS 'Numéro de la tranche de prix appliquée';
COMMENT ON COLUMN achats.prix_unitaire IS 'Prix unitaire appliqué';
COMMENT ON COLUMN achats.nom_client IS 'Nom complet du client';
COMMENT ON COLUMN achats.adresse_ip IS 'Adresse IP de la transaction';
COMMENT ON COLUMN achats.localisation IS 'Localisation de la transaction';
COMMENT ON COLUMN achats.statut IS 'Statut de la transaction (SUCCESS, ECHEC)';
COMMENT ON COLUMN achats.date_achat IS 'Date de l''achat';
COMMENT ON COLUMN achats.heure_achat IS 'Heure de l''achat';
