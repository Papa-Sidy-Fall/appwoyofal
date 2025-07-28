-- Seeder: Insertion des clients de test
-- Date: 2025-01-27
-- Description: Insertion des clients de test pour l'application Woyofal

-- Insertion des clients de test
INSERT INTO clients (nom, prenom, telephone, adresse) 
VALUES 
    ('Fall', 'Papa Sidy', '77123456789', 'Dakar, Plateau'),
    ('Diop', 'Aminata', '77234567890', 'Thiès, Centre-ville'),
    ('Ndiaye', 'Moussa', '77345678901', 'Saint-Louis, Sor'),
    ('Seck', 'Fatou', '77456789012', 'Kaolack, Médina'),
    ('Ba', 'Ousmane', '77567890123', 'Ziguinchor, Centre'),
    ('Sarr', 'Awa', '77678901234', 'Tambacounda, Quartier résidentiel'),
    ('Mbaye', 'Ibrahima', '77789012345', 'Diourbel, Escale'),
    ('Diallo', 'Mariama', '77890123456', 'Kolda, Centre-ville'),
    ('Kane', 'Abdoul', '77901234567', 'Matam, Plateau'),
    ('Ly', 'Aïssatou', '77012345678', 'Kédougou, Centre')
ON CONFLICT (telephone) DO UPDATE SET
    nom = EXCLUDED.nom,
    prenom = EXCLUDED.prenom,
    adresse = EXCLUDED.adresse,
    date_modification = CURRENT_TIMESTAMP;

-- Vérification de l'insertion
SELECT id, nom, prenom, telephone, adresse 
FROM clients 
ORDER BY nom, prenom;
