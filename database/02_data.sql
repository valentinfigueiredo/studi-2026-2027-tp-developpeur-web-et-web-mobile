-- =====================================================================
--  Vite & Gourmand - Jeu de données de démonstration
--  À exécuter APRÈS 01_schema.sql
--
--  Comptes de test (mots de passe hachés en bcrypt avec password_hash) :
--    Administrateur : jose@vite-gourmand.fr      / Admin@Vite2026!
--    Employée       : julie@vite-gourmand.fr     / Employe@Vite2026!
--    Utilisateur    : marie.dupont@example.com   / Client@Vite2026!
--    Utilisateur    : thomas.martin@example.com  / Client@Vite2026!
--    Employé désactivé (ne peut plus se connecter) : ancien.employe@vite-gourmand.fr
-- =====================================================================

USE vite_gourmand;

-- ---------------------------------------------------------------------
--  Rôles
-- ---------------------------------------------------------------------
INSERT INTO role (role_id, libelle) VALUES
    (1, 'utilisateur'),
    (2, 'employe'),
    (3, 'administrateur');

-- ---------------------------------------------------------------------
--  Utilisateurs
--  Le compte administrateur n'est créable QUE par ce script (exigence de José).
-- ---------------------------------------------------------------------
INSERT INTO utilisateur (utilisateur_id, email, password, prenom, nom, telephone, adresse_postale, code_postal, ville, role_id, actif) VALUES
    (1, 'jose@vite-gourmand.fr',
        '$2y$12$IkV31yzD8dwzx/217fhSbuHz9jLiMt6eaJJ9Ai2m95z01dFb4b4jK',
        'José', 'Garcia', '0601020304', '12 rue Sainte-Catherine', '33000', 'Bordeaux', 3, TRUE),
    (2, 'julie@vite-gourmand.fr',
        '$2y$12$JmGKPxLQop0ieUnm23JVjOtGt4TAuhlGxEK.iXXzYr9/swn57/rCy',
        'Julie', 'Moreau', '0605060708', '12 rue Sainte-Catherine', '33000', 'Bordeaux', 2, TRUE),
    (3, 'ancien.employe@vite-gourmand.fr',
        '$2y$12$JmGKPxLQop0ieUnm23JVjOtGt4TAuhlGxEK.iXXzYr9/swn57/rCy',
        'Paul', 'Lefèvre', NULL, NULL, NULL, NULL, 2, FALSE),
    (4, 'marie.dupont@example.com',
        '$2y$12$/pT/2/WY8xk.9XdSpvK.AuaONKikrrCkp7p589iTEeH3Db5WUP0xW',
        'Marie', 'Dupont', '0611223344', '8 cours de l''Intendance', '33000', 'Bordeaux', 1, TRUE),
    (5, 'thomas.martin@example.com',
        '$2y$12$/pT/2/WY8xk.9XdSpvK.AuaONKikrrCkp7p589iTEeH3Db5WUP0xW',
        'Thomas', 'Martin', '0655667788', '25 avenue de la Libération', '33400', 'Talence', 1, TRUE);

-- ---------------------------------------------------------------------
--  Référentiels : thèmes, régimes, allergènes
-- ---------------------------------------------------------------------
INSERT INTO theme (theme_id, libelle) VALUES
    (1, 'Noël'),
    (2, 'Pâques'),
    (3, 'Classique'),
    (4, 'Événement');

INSERT INTO regime (regime_id, libelle) VALUES
    (1, 'Classique'),
    (2, 'Végétarien'),
    (3, 'Vegan'),
    (4, 'Sans gluten');

-- Les 14 allergènes à déclaration obligatoire (règlement européen INCO 1169/2011)
INSERT INTO allergene (allergene_id, libelle) VALUES
    (1,  'Gluten'),
    (2,  'Crustacés'),
    (3,  'Œufs'),
    (4,  'Poissons'),
    (5,  'Arachides'),
    (6,  'Soja'),
    (7,  'Lait'),
    (8,  'Fruits à coque'),
    (9,  'Céleri'),
    (10, 'Moutarde'),
    (11, 'Graines de sésame'),
    (12, 'Anhydride sulfureux et sulfites'),
    (13, 'Lupin'),
    (14, 'Mollusques');

-- ---------------------------------------------------------------------
--  Menus
--  prix_par_personne x nombre_personne_minimum = prix affiché « à partir de »
-- ---------------------------------------------------------------------
INSERT INTO menu (menu_id, titre, description, theme_id, regime_id, nombre_personne_minimum, prix_par_personne, conditions, quantite_restante) VALUES
    (1, 'Réveillon de Noël',
        'Un menu de fête généreux autour du foie gras, du chapon rôti et de la traditionnelle bûche, pour un réveillon sans stress.',
        1, 1, 8, 45.00,
        'À commander au minimum 15 jours avant la prestation. À conserver au réfrigérateur entre 0 et 4 °C et à consommer dans les 24 heures.',
        5),
    (2, 'Brunch de Pâques',
        'Un brunch familial et printanier : agneau de sept heures, légumes primeurs et nid de Pâques au chocolat.',
        2, 1, 10, 42.00,
        'À commander au minimum 10 jours avant la prestation. Vaisselle prêtée sur demande (à restituer sous 10 jours ouvrés).',
        8),
    (3, 'Le Bordelais classique',
        'Les incontournables de la cuisine bordelaise : entrecôte à la bordelaise, gratin dauphinois et cannelés maison.',
        3, 1, 6, 30.00,
        'À commander au minimum 72 heures avant la prestation. À conserver au réfrigérateur entre 0 et 4 °C.',
        20),
    (4, 'Jardin vegan',
        'Un menu 100 % végétal, coloré et de saison : velouté de potimarron, curry de légumes et mousse au chocolat à l''aquafaba.',
        3, 3, 4, 35.00,
        'À commander au minimum 48 heures avant la prestation. Préparé dans un espace dédié, mais traces d''allergènes possibles.',
        15),
    (5, 'Cocktail d''entreprise',
        'Un buffet dînatoire pour vos événements professionnels : pièces salées, mini-burgers et mignardises.',
        4, 2, 20, 35.00,
        'À commander au minimum 3 semaines avant la prestation. Matériel de service prêté (à restituer sous 10 jours ouvrés, sinon 600 € de frais).',
        3);

-- Galerie d'images (texte alternatif obligatoire pour l'accessibilité RGAA)
INSERT INTO menu_image (menu_id, chemin, texte_alt, ordre) VALUES
    (1, 'assets/img/menus/noel-1.webp',     'Table de réveillon dressée avec foie gras et chapon rôti', 1),
    (1, 'assets/img/menus/noel-2.webp',     'Bûche de Noël au chocolat décorée', 2),
    (2, 'assets/img/menus/paques-1.webp',   'Gigot d''agneau entouré de légumes primeurs', 1),
    (2, 'assets/img/menus/paques-2.webp',   'Nid de Pâques en chocolat garni de petits œufs', 2),
    (3, 'assets/img/menus/bordelais-1.webp','Entrecôte à la bordelaise servie avec un gratin dauphinois', 1),
    (3, 'assets/img/menus/bordelais-2.webp','Assiette de cannelés bordelais dorés', 2),
    (4, 'assets/img/menus/vegan-1.webp',    'Bol de curry de légumes colorés', 1),
    (5, 'assets/img/menus/cocktail-1.webp', 'Buffet de pièces cocktail salées et sucrées', 1);

-- ---------------------------------------------------------------------
--  Plats (certains sont partagés entre plusieurs menus)
-- ---------------------------------------------------------------------
INSERT INTO plat (plat_id, titre_plat, type_plat, description) VALUES
    (1,  'Foie gras maison et chutney de figues', 'entree',  'Foie gras mi-cuit, pain d''épices toasté.'),
    (2,  'Velouté de potimarron',                 'entree',  'Velouté onctueux au lait de coco.'),
    (3,  'Salade de chèvre chaud',                'entree',  'Toasts de chèvre, miel et noix.'),
    (4,  'Chapon rôti aux marrons',               'plat',    'Chapon fermier, farce aux marrons.'),
    (5,  'Agneau de sept heures',                 'plat',    'Épaule d''agneau confite, légumes primeurs.'),
    (6,  'Entrecôte à la bordelaise',             'plat',    'Sauce au vin rouge et échalotes.'),
    (7,  'Curry de légumes de saison',            'plat',    'Riz basmati, légumes du marché.'),
    (8,  'Gratin dauphinois',                     'plat',    'Accompagnement crème et ail.'),
    (9,  'Bûche de Noël chocolat-praliné',        'dessert', 'Biscuit chocolat, croustillant praliné.'),
    (10, 'Nid de Pâques au chocolat',             'dessert', 'Chocolat noir et petits œufs en sucre.'),
    (11, 'Cannelés bordelais',                    'dessert', 'Recette traditionnelle au rhum et à la vanille.'),
    (12, 'Mousse au chocolat vegan',              'dessert', 'Mousse à l''aquafaba, sans produit animal.'),
    (13, 'Mini-burgers végétariens',              'plat',    'Galette de légumes, cheddar, pain brioché.'),
    (14, 'Assortiment de mignardises',            'dessert', 'Macarons, tartelettes et choux.');

-- Composition des menus
INSERT INTO menu_plat (menu_id, plat_id) VALUES
    (1, 1), (1, 4), (1, 8), (1, 9),          -- Noël
    (2, 3), (2, 5), (2, 10),                 -- Pâques
    (3, 3), (3, 6), (3, 8), (3, 11),         -- Bordelais (chèvre et gratin partagés)
    (4, 2), (4, 7), (4, 12),                 -- Vegan
    (5, 3), (5, 13), (5, 11), (5, 14);       -- Cocktail (cannelés partagés)

-- Allergènes des plats
INSERT INTO plat_allergene (plat_id, allergene_id) VALUES
    (1, 1), (1, 12),                         -- foie gras : gluten (pain d'épices), sulfites
    (2, 9),                                  -- velouté : céleri
    (3, 1), (3, 7), (3, 8),                  -- chèvre chaud : gluten, lait, fruits à coque
    (4, 1), (4, 7), (4, 8),                  -- chapon : gluten, lait, fruits à coque (marrons dans la farce)
    (5, 9), (5, 12),                         -- agneau : céleri, sulfites
    (6, 12),                                 -- entrecôte : sulfites (vin)
    (7, 6), (7, 10),                         -- curry : soja, moutarde
    (8, 7),                                  -- gratin : lait
    (9, 1), (9, 3), (9, 7), (9, 8),          -- bûche : gluten, œufs, lait, fruits à coque
    (10, 7), (10, 6),                        -- nid de Pâques : lait, soja
    (11, 1), (11, 3), (11, 7),               -- cannelés : gluten, œufs, lait
    (12, 6),                                 -- mousse vegan : soja
    (13, 1), (13, 3), (13, 7), (13, 11),     -- mini-burgers : gluten, œufs, lait, sésame
    (14, 1), (14, 3), (14, 7), (14, 8);      -- mignardises : gluten, œufs, lait, fruits à coque

-- ---------------------------------------------------------------------
--  Horaires (pied de page)
-- ---------------------------------------------------------------------
INSERT INTO horaire (jour, heure_ouverture, heure_fermeture, ferme) VALUES
    ('lundi',    NULL,       NULL,       TRUE),
    ('mardi',    '09:00:00', '18:00:00', FALSE),
    ('mercredi', '09:00:00', '18:00:00', FALSE),
    ('jeudi',    '09:00:00', '18:00:00', FALSE),
    ('vendredi', '09:00:00', '19:00:00', FALSE),
    ('samedi',   '10:00:00', '19:00:00', FALSE),
    ('dimanche', '10:00:00', '13:00:00', FALSE);

-- ---------------------------------------------------------------------
--  Commandes (un exemple par statut important)
--  Règles de calcul :
--    prix_menu         = prix_par_personne x nombre_personne
--    montant_reduction = 10 % de prix_menu si nombre_personne >= nb_min + 5
--    prix_livraison    = 0 à Bordeaux, sinon 5 € + 0,59 € x distance_km
--    prix_total        = prix_menu - montant_reduction + prix_livraison
-- ---------------------------------------------------------------------
INSERT INTO commande (commande_id, numero_commande, utilisateur_id, menu_id, date_commande,
                      date_prestation, heure_livraison, adresse_prestation, code_postal, ville_prestation, distance_km,
                      nombre_personne, prix_menu, montant_reduction, prix_livraison, prix_total,
                      statut, pret_materiel, motif_annulation, mode_contact, annulee_par) VALUES
    -- 1. Marie, Noël, 13 pers. (8 + 5 => réduction 10 %), Bordeaux, terminée
    (1, 'CMD-2025-00001', 4, 1, '2025-12-01 10:15:00',
        '2025-12-24', '18:00:00', '8 cours de l''Intendance', '33000', 'Bordeaux', 0,
        13, 585.00, 58.50, 0.00, 526.50,
        'terminee', FALSE, NULL, NULL, NULL),
    -- 2. Marie, Bordelais, 6 pers., Mérignac 8 km => 5 + 0,59 x 8 = 9,72 €, acceptée
    (2, 'CMD-2026-00002', 4, 3, '2026-11-10 14:30:00',
        '2026-11-28', '12:00:00', '3 avenue de l''Yser', '33700', 'Mérignac', 8,
        6, 180.00, 0.00, 9.72, 189.72,
        'accepte', FALSE, NULL, NULL, NULL),
    -- 3. Marie, Vegan, 5 pers., Bordeaux, en attente (encore annulable/modifiable par le client)
    (3, 'CMD-2026-00003', 4, 4, '2026-11-15 09:00:00',
        '2026-11-25', '19:30:00', '8 cours de l''Intendance', '33000', 'Bordeaux', 0,
        5, 175.00, 0.00, 0.00, 175.00,
        'en_attente', FALSE, NULL, NULL, NULL),
    -- 4. Thomas, Pâques, 10 pers., Pessac 10 km => 5 + 5,90 = 10,90 €, matériel prêté non rendu
    (4, 'CMD-2026-00004', 5, 2, '2026-03-20 11:00:00',
        '2026-04-05', '11:30:00', '14 rue Pasteur', '33600', 'Pessac', 10,
        10, 420.00, 0.00, 10.90, 430.90,
        'en_attente_retour_materiel', TRUE, NULL, NULL, NULL),
    -- 5. Thomas, Cocktail, 20 pers., Bordeaux, annulée par un employé après appel
    (5, 'CMD-2026-00005', 5, 5, '2026-10-01 16:45:00',
        '2026-10-15', '18:30:00', '40 quai des Chartrons', '33000', 'Bordeaux', 0,
        20, 700.00, 0.00, 0.00, 700.00,
        'annulee', FALSE, 'Délai de commande de 3 semaines non respecté, client prévenu et d''accord.', 'telephone', 'employe'),
    -- 6. Thomas, Bordelais, 11 pers. (6 + 5 => réduction 10 %), Bordeaux, terminée
    (6, 'CMD-2026-00006', 5, 3, '2026-06-02 08:20:00',
        '2026-06-14', '12:30:00', '40 quai des Chartrons', '33000', 'Bordeaux', 0,
        11, 330.00, 33.00, 0.00, 297.00,
        'terminee', FALSE, NULL, NULL, NULL),
    -- 7. Thomas, Vegan, 4 pers., Talence 6 km => 5 + 3,54 = 8,54 €, terminée
    (7, 'CMD-2026-00007', 5, 4, '2026-09-05 19:10:00',
        '2026-09-12', '20:00:00', '25 avenue de la Libération', '33400', 'Talence', 6,
        4, 140.00, 0.00, 8.54, 148.54,
        'terminee', FALSE, NULL, NULL, NULL);

-- Suivi des commandes (historique des statuts avec date et heure)
INSERT INTO suivi_commande (commande_id, statut, date_modification, modifie_par) VALUES
    (1, 'en_attente',            '2025-12-01 10:15:00', NULL),
    (1, 'accepte',               '2025-12-01 15:00:00', 2),
    (1, 'en_preparation',        '2025-12-24 09:00:00', 2),
    (1, 'en_cours_de_livraison', '2025-12-24 17:15:00', 2),
    (1, 'livre',                 '2025-12-24 17:55:00', 2),
    (1, 'terminee',              '2025-12-24 18:00:00', 2),
    (2, 'en_attente',            '2026-11-10 14:30:00', NULL),
    (2, 'accepte',               '2026-11-11 09:30:00', 2),
    (3, 'en_attente',            '2026-11-15 09:00:00', NULL),
    (4, 'en_attente',            '2026-03-20 11:00:00', NULL),
    (4, 'accepte',               '2026-03-20 17:00:00', 2),
    (4, 'en_preparation',        '2026-04-05 07:30:00', 2),
    (4, 'en_cours_de_livraison', '2026-04-05 10:45:00', 2),
    (4, 'livre',                 '2026-04-05 11:25:00', 2),
    (4, 'en_attente_retour_materiel', '2026-04-05 11:30:00', 2),
    (5, 'en_attente',            '2026-10-01 16:45:00', NULL),
    (5, 'annulee',               '2026-10-02 10:00:00', 1),
    (6, 'en_attente',            '2026-06-02 08:20:00', NULL),
    (6, 'accepte',               '2026-06-02 14:00:00', 2),
    (6, 'en_preparation',        '2026-06-14 08:00:00', 2),
    (6, 'en_cours_de_livraison', '2026-06-14 11:45:00', 2),
    (6, 'livre',                 '2026-06-14 12:20:00', 2),
    (6, 'terminee',              '2026-06-14 12:30:00', 2),
    (7, 'en_attente',            '2026-09-05 19:10:00', NULL),
    (7, 'accepte',               '2026-09-06 10:00:00', 1),
    (7, 'en_preparation',        '2026-09-12 16:00:00', 2),
    (7, 'en_cours_de_livraison', '2026-09-12 19:20:00', 2),
    (7, 'livre',                 '2026-09-12 19:55:00', 2),
    (7, 'terminee',              '2026-09-12 20:00:00', 2);

-- ---------------------------------------------------------------------
--  Avis (uniquement sur des commandes terminées)
-- ---------------------------------------------------------------------
INSERT INTO avis (commande_id, utilisateur_id, note, description, statut, date_avis) VALUES
    (1, 4, 5, 'Un réveillon parfait, le chapon était fondant et la livraison pile à l''heure. Merci Julie et José !', 'valide',     '2025-12-26 11:00:00'),
    (6, 5, 4, 'Entrecôte excellente et cannelés comme à Bordeaux. Petit bémol sur le gratin un peu tiède.',        'valide',     '2026-06-15 09:30:00'),
    (7, 5, 3, 'Bon curry mais portions un peu justes pour 4.',                                                       'en_attente', '2026-09-13 10:00:00');
