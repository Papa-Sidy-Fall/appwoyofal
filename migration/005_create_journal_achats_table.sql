-- Migration: Création de la table journal_achats
-- Date: 2025-01-27
-- Description: Table pour la journalisation de toutes les tentatives d'achat

CREATE TABLE IF NOT EXISTS journal_achats (
    id SERIAL PRIMARY KEY,
    reference VARCHAR(50),
    numero_compteur VARCHAR(50),
    montant DECIMAL(10,2),
    nbre_kwt DECIMAL(10,3),
    statut VARCHAR(20) CHECK (statut IN ('SUCCESS', 'ECHEC')),
    adresse_ip INET,
    localisation VARCHAR(100),
    date_creation DATE DEFAULT CURRENT_DATE,
    heure_creation TIME DEFAULT CURRENT_TIME,
    code_recharge VARCHAR(20),
    nom_client VARCHAR(200),
    tranche_numero INTEGER,
    prix_unitaire DECIMAL(8,2),
    user_agent TEXT,
    session_id VARCHAR(100),
    
    -- Pas de contraintes strictes pour permettre la journalisation même en cas d'erreur
    CONSTRAINT chk_journal_statut CHECK (statut IN ('SUCCESS', 'ECHEC'))
);

-- Index pour optimiser les recherches et les rapports
CREATE INDEX IF NOT EXISTS idx_journal_reference ON journal_achats(reference);
CREATE INDEX IF NOT EXISTS idx_journal_compteur ON journal_achats(numero_compteur);
CREATE INDEX IF NOT EXISTS idx_journal_date ON journal_achats(date_creation, heure_creation);
CREATE INDEX IF NOT EXISTS idx_journal_statut ON journal_achats(statut);
CREATE INDEX IF NOT EXISTS idx_journal_ip ON journal_achats(adresse_ip);
CREATE INDEX IF NOT EXISTS idx_journal_montant ON journal_achats(montant);

-- Commentaires sur les colonnes
COMMENT ON TABLE journal_achats IS 'Table de journalisation de toutes les tentatives d''achat';
COMMENT ON COLUMN journal_achats.id IS 'Identifiant unique du log';
COMMENT ON COLUMN journal_achats.reference IS 'Référence de l''achat (si généré)';
COMMENT ON COLUMN journal_achats.numero_compteur IS 'Numéro du compteur demandé';
COMMENT ON COLUMN journal_achats.montant IS 'Montant demandé';
COMMENT ON COLUMN journal_achats.nbre_kwt IS 'Nombre de kWh calculé';
COMMENT ON COLUMN journal_achats.statut IS 'Résultat de la tentative';
COMMENT ON COLUMN journal_achats.adresse_ip IS 'Adresse IP du client';
COMMENT ON COLUMN journal_achats.localisation IS 'Localisation de la demande';
COMMENT ON COLUMN journal_achats.date_creation IS 'Date de la tentative';
COMMENT ON COLUMN journal_achats.heure_creation IS 'Heure de la tentative';
COMMENT ON COLUMN journal_achats.code_recharge IS 'Code de recharge (si généré)';
COMMENT ON COLUMN journal_achats.nom_client IS 'Nom du client';
COMMENT ON COLUMN journal_achats.tranche_numero IS 'Tranche appliquée';
COMMENT ON COLUMN journal_achats.prix_unitaire IS 'Prix unitaire appliqué';
COMMENT ON COLUMN journal_achats.user_agent IS 'User agent du navigateur';
COMMENT ON COLUMN journal_achats.session_id IS 'ID de session';
