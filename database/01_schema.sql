-- =====================================================================
--  Vite & Gourmand - Script de création de la base de données
--  SGBD : MySQL 8 / MariaDB 10.6+
--  Encodage : utf8mb4 (gère les accents et les emojis)
--  Moteur : InnoDB (clés étrangères + transactions)
-- =====================================================================

-- Le script s'exécute dans la base sélectionnée à la connexion (ex : mysql ... vite_gourmand < 01_schema.sql).
-- Il ne crée pas la base lui-même : chez Alwaysdata, la base est créée depuis l'interface
-- et son nom est préfixé par le nom du compte.
-- Relancer ce script supprime et recrée toutes les tables.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS tentative_connexion;
DROP TABLE IF EXISTS avis;
DROP TABLE IF EXISTS suivi_commande;
DROP TABLE IF EXISTS commande;
DROP TABLE IF EXISTS horaire;
DROP TABLE IF EXISTS plat_allergene;
DROP TABLE IF EXISTS allergene;
DROP TABLE IF EXISTS menu_plat;
DROP TABLE IF EXISTS plat;
DROP TABLE IF EXISTS menu_image;
DROP TABLE IF EXISTS menu;
DROP TABLE IF EXISTS regime;
DROP TABLE IF EXISTS theme;
DROP TABLE IF EXISTS reinitialisation_mdp;
DROP TABLE IF EXISTS utilisateur;
DROP TABLE IF EXISTS role;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
--  1. Utilisateurs et rôles
-- ---------------------------------------------------------------------

-- Les 3 rôles de l'application. Table séparée plutôt qu'un ENUM :
-- on peut ajouter un rôle sans modifier la structure.
CREATE TABLE role (
    role_id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    libelle   VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE utilisateur (
    utilisateur_id  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email           VARCHAR(255) NOT NULL UNIQUE,      -- sert d'identifiant (username)
    password        VARCHAR(255) NOT NULL,             -- hash bcrypt (password_hash), JAMAIS en clair
    prenom          VARCHAR(100) NOT NULL,
    nom             VARCHAR(100) NOT NULL,
    telephone       VARCHAR(20)  NULL,                 -- GSM (NULL possible pour un employé)
    adresse_postale VARCHAR(255) NULL,
    code_postal     VARCHAR(10)  NULL,
    ville           VARCHAR(100) NULL,
    pays            VARCHAR(100) NOT NULL DEFAULT 'France',
    role_id         INT UNSIGNED NOT NULL,
    actif           BOOLEAN NOT NULL DEFAULT TRUE,     -- FALSE = compte désactivé (départ d'un employé)
    date_creation   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_utilisateur_role
        FOREIGN KEY (role_id) REFERENCES role(role_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Jetons de réinitialisation du mot de passe ("mot de passe oublié").
-- On stocke le HASH du jeton (comme un mot de passe) : si la base fuite,
-- les liens envoyés par mail restent inutilisables.
CREATE TABLE reinitialisation_mdp (
    reinitialisation_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id      INT UNSIGNED NOT NULL,
    token_hash          CHAR(64) NOT NULL UNIQUE,      -- SHA-256 du jeton envoyé par mail
    date_expiration     DATETIME NOT NULL,             -- validité courte (ex : 1 heure)
    utilise             BOOLEAN NOT NULL DEFAULT FALSE,
    CONSTRAINT fk_reinit_utilisateur
        FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(utilisateur_id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  2. Menus, plats, allergènes
-- ---------------------------------------------------------------------

CREATE TABLE theme (
    theme_id  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    libelle   VARCHAR(50) NOT NULL UNIQUE            -- Noël, Pâques, Classique, Événement
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE regime (
    regime_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    libelle   VARCHAR(50) NOT NULL UNIQUE            -- Végétarien, Vegan, Classique, Sans gluten...
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE menu (
    menu_id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titre                   VARCHAR(150) NOT NULL,
    description             TEXT NOT NULL,
    theme_id                INT UNSIGNED NOT NULL,
    regime_id               INT UNSIGNED NOT NULL,
    nombre_personne_minimum INT UNSIGNED NOT NULL,
    prix_par_personne       DECIMAL(8,2) NOT NULL,     -- prix d'une part (MCD de l'énoncé)
    conditions              TEXT NOT NULL,             -- délai de commande, stockage... (affiché en évidence)
    quantite_restante       INT UNSIGNED NOT NULL DEFAULT 0, -- stock : nb de commandes encore possibles
    actif                   BOOLEAN NOT NULL DEFAULT TRUE,
    date_creation           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_menu_theme  FOREIGN KEY (theme_id)  REFERENCES theme(theme_id),
    CONSTRAINT fk_menu_regime FOREIGN KEY (regime_id) REFERENCES regime(regime_id),
    CONSTRAINT chk_menu_personnes CHECK (nombre_personne_minimum >= 1),
    CONSTRAINT chk_menu_prix      CHECK (prix_par_personne > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Galerie d'images d'un menu (1 menu -> N images).
CREATE TABLE menu_image (
    image_id      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    menu_id       INT UNSIGNED NOT NULL,
    chemin        VARCHAR(255) NOT NULL,              -- chemin du fichier (ex : uploads/menus/noel-1.webp)
    texte_alt     VARCHAR(255) NOT NULL,              -- texte alternatif obligatoire (RGAA)
    ordre         TINYINT UNSIGNED NOT NULL DEFAULT 0,
    CONSTRAINT fk_image_menu
        FOREIGN KEY (menu_id) REFERENCES menu(menu_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE plat (
    plat_id     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titre_plat  VARCHAR(150) NOT NULL,
    type_plat   ENUM('entree', 'plat', 'dessert') NOT NULL,
    description TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Un plat peut être dans plusieurs menus, un menu contient plusieurs plats (N-N).
CREATE TABLE menu_plat (
    menu_id  INT UNSIGNED NOT NULL,
    plat_id  INT UNSIGNED NOT NULL,
    PRIMARY KEY (menu_id, plat_id),
    CONSTRAINT fk_menuplat_menu FOREIGN KEY (menu_id) REFERENCES menu(menu_id) ON DELETE CASCADE,
    CONSTRAINT fk_menuplat_plat FOREIGN KEY (plat_id) REFERENCES plat(plat_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE allergene (
    allergene_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    libelle      VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Un plat a plusieurs allergènes, un allergène concerne plusieurs plats (N-N).
CREATE TABLE plat_allergene (
    plat_id      INT UNSIGNED NOT NULL,
    allergene_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (plat_id, allergene_id),
    CONSTRAINT fk_platall_plat      FOREIGN KEY (plat_id)      REFERENCES plat(plat_id)      ON DELETE CASCADE,
    CONSTRAINT fk_platall_allergene FOREIGN KEY (allergene_id) REFERENCES allergene(allergene_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  3. Horaires (affichés dans le pied de page, modifiables par un employé)
-- ---------------------------------------------------------------------

CREATE TABLE horaire (
    horaire_id      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    jour            ENUM('lundi','mardi','mercredi','jeudi','vendredi','samedi','dimanche') NOT NULL UNIQUE,
    heure_ouverture TIME NULL,
    heure_fermeture TIME NULL,
    ferme           BOOLEAN NOT NULL DEFAULT FALSE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  4. Commandes et suivi
-- ---------------------------------------------------------------------

CREATE TABLE commande (
    commande_id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    numero_commande     VARCHAR(20) NOT NULL UNIQUE,   -- ex : CMD-2026-00001 (affiché au client)
    utilisateur_id      INT UNSIGNED NOT NULL,
    menu_id             INT UNSIGNED NOT NULL,
    date_commande       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    -- Prestation
    date_prestation     DATE NOT NULL,
    heure_livraison     TIME NOT NULL,
    adresse_prestation  VARCHAR(255) NOT NULL,
    code_postal         VARCHAR(10) NOT NULL,
    ville_prestation    VARCHAR(100) NOT NULL,
    distance_km         DECIMAL(6,2) NOT NULL DEFAULT 0, -- distance depuis Bordeaux (0 si Bordeaux)
    nombre_personne     INT UNSIGNED NOT NULL,
    -- Prix figés au moment de la commande (le prix du menu peut changer ensuite)
    prix_menu           DECIMAL(10,2) NOT NULL,        -- avant réduction
    montant_reduction   DECIMAL(10,2) NOT NULL DEFAULT 0, -- 10 % si nb personnes >= minimum + 5
    prix_livraison      DECIMAL(10,2) NOT NULL DEFAULT 0, -- 0 à Bordeaux, sinon 5 € + 0,59 €/km
    prix_total          DECIMAL(10,2) NOT NULL,
    statut              ENUM(
                            'en_attente',
                            'accepte',
                            'en_preparation',
                            'en_cours_de_livraison',
                            'livre',
                            'en_attente_retour_materiel',
                            'terminee',
                            'annulee'
                        ) NOT NULL DEFAULT 'en_attente',
    pret_materiel       BOOLEAN NOT NULL DEFAULT FALSE,
    restitution_materiel BOOLEAN NOT NULL DEFAULT FALSE, -- TRUE quand le matériel prêté a été rendu
    -- Annulation : motif toujours obligatoire ; mode de contact obligatoire si c'est un employé qui annule
    motif_annulation    TEXT NULL,
    mode_contact        ENUM('telephone', 'mail') NULL,
    annulee_par         ENUM('client', 'employe') NULL,
    CONSTRAINT fk_commande_utilisateur FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(utilisateur_id),
    CONSTRAINT fk_commande_menu        FOREIGN KEY (menu_id)        REFERENCES menu(menu_id),
    CONSTRAINT chk_commande_personnes  CHECK (nombre_personne >= 1),
    -- Une commande annulée a toujours un motif ; si un employé l'annule, le mode de contact est obligatoire
    CONSTRAINT chk_commande_annulation CHECK (
        statut <> 'annulee'
        OR (annulee_par = 'client'  AND motif_annulation IS NOT NULL)
        OR (annulee_par = 'employe' AND motif_annulation IS NOT NULL AND mode_contact IS NOT NULL)
    )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Historique des changements de statut = "suivi de commande" côté client.
CREATE TABLE suivi_commande (
    suivi_id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    commande_id       INT UNSIGNED NOT NULL,
    statut            VARCHAR(30) NOT NULL,
    date_modification DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    modifie_par       INT UNSIGNED NULL,               -- employé/admin ayant fait le changement
    CONSTRAINT fk_suivi_commande FOREIGN KEY (commande_id) REFERENCES commande(commande_id) ON DELETE CASCADE,
    CONSTRAINT fk_suivi_employe  FOREIGN KEY (modifie_par) REFERENCES utilisateur(utilisateur_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  5. Avis clients
-- ---------------------------------------------------------------------

-- 1 avis maximum par commande, déposé quand la commande est "terminee".
-- Visible sur l'accueil uniquement après validation par un employé.
CREATE TABLE avis (
    avis_id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    commande_id     INT UNSIGNED NOT NULL UNIQUE,
    utilisateur_id  INT UNSIGNED NOT NULL,
    note            TINYINT UNSIGNED NOT NULL,
    description     TEXT NOT NULL,                 -- commentaire du client
    statut          ENUM('en_attente', 'valide', 'refuse') NOT NULL DEFAULT 'en_attente',
    date_avis       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_avis_commande    FOREIGN KEY (commande_id)    REFERENCES commande(commande_id) ON DELETE CASCADE,
    CONSTRAINT fk_avis_utilisateur FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(utilisateur_id),
    CONSTRAINT chk_avis_note CHECK (note BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  6. Index pour accélérer les filtres
-- ---------------------------------------------------------------------

-- Filtres de la vue globale des menus (prix, thème, régime, nb personnes)
CREATE INDEX idx_menu_filtres ON menu (actif, theme_id, regime_id, nombre_personne_minimum);
-- Filtre des commandes dans l'espace employé (par statut, par client)
CREATE INDEX idx_commande_statut      ON commande (statut);
CREATE INDEX idx_commande_utilisateur ON commande (utilisateur_id);
-- Avis validés affichés sur la page d'accueil
CREATE INDEX idx_avis_statut ON avis (statut);

-- ---------------------------------------------------------------------
--  7. Sécurité : tentatives de connexion échouées (anti brute-force)
--  Au-delà de 5 échecs en 15 minutes pour un même email, la connexion est bloquée.
-- ---------------------------------------------------------------------
CREATE TABLE tentative_connexion (
    tentative_id    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email           VARCHAR(255) NOT NULL,
    ip              VARCHAR(45) NOT NULL,              -- 45 caractères pour une IPv6
    date_tentative  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tentative_email_date (email, date_tentative)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
