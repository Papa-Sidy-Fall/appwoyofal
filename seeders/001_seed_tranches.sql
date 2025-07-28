-- Seeder: Insertion des tranches par défaut
-- Date: 2025-01-27
-- Description: Insertion des tranches de prix par défaut du système Woyofal

-- Supprimer les données existantes (optionnel)
-- DELETE FROM tranches;

-- Insertion des tranches par défaut
INSERT INTO tranches (numero, seuil_min, seuil_max, prix_unitaire, description, actif) 
VALUES 
    (1, 0.00, 150.00, 79.99, 'Tranche 1: 0-150 kWh - Tarif social', true),
    (2, 150.00, 250.00, 89.99, 'Tranche 2: 150-250 kWh - Tarif normal', true),
    (3, 250.00, 0.00, 99.99, 'Tranche 3: >250 kWh - Tarif supérieur', true)
ON CONFLICT (numero) DO UPDATE SET
    seuil_min = EXCLUDED.seuil_min,
    seuil_max = EXCLUDED.seuil_max,
    prix_unitaire = EXCLUDED.prix_unitaire,
    description = EXCLUDED.description,
    actif = EXCLUDED.actif,
    date_modification = CURRENT_TIMESTAMP;

-- Vérification de l'insertion
SELECT numero, seuil_min, seuil_max, prix_unitaire, description, actif 
FROM tranches 
ORDER BY numero;
