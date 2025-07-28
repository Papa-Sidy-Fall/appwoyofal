-- Seeder: Insertion des compteurs de test
-- Date: 2025-01-27
-- Description: Insertion des compteurs de test pour l'application Woyofal

-- Insertion des compteurs de test (associés aux clients)
INSERT INTO compteurs (numero, client_id, statut, solde_actuel) 
VALUES 
    ('WYF001000001', 1, 'ACTIF', 125.50),
    ('WYF001000002', 2, 'ACTIF', 89.25),
    ('WYF001000003', 3, 'ACTIF', 200.75),
    ('WYF001000004', 4, 'ACTIF', 45.00),
    ('WYF001000005', 5, 'ACTIF', 156.80),
    ('WYF001000006', 6, 'ACTIF', 300.25),
    ('WYF001000007', 7, 'ACTIF', 75.60),
    ('WYF001000008', 8, 'ACTIF', 180.90),
    ('WYF001000009', 9, 'INACTIF', 0.00),
    ('WYF001000010', 10, 'ACTIF', 220.40)
ON CONFLICT (numero) DO UPDATE SET
    statut = EXCLUDED.statut,
    solde_actuel = EXCLUDED.solde_actuel,
    date_modification = CURRENT_TIMESTAMP;

-- Vérification de l'insertion
SELECT c.numero, c.statut, c.solde_actuel, cl.nom, cl.prenom 
FROM compteurs c
JOIN clients cl ON c.client_id = cl.id
ORDER BY c.numero;
