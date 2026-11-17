# Vite & Gourmand

Application web de commande de menus traiteur pour **Vite & Gourmand**, traiteur bordelais depuis 25 ans.
Projet réalisé par FastDev.

- **Visiteurs** : consultation des menus avec filtres dynamiques, détail des menus, contact.
- **Clients** : commande en ligne avec calcul du prix en direct, suivi de commande, avis.
- **Employés** : gestion des commandes, menus, plats, horaires, modération des avis.
- **Administrateur** : gestion des comptes employés, statistiques (MongoDB) et chiffre d'affaires.

| | |
|---|---|
| Application en ligne | `https://vite-et-gourmand.fly.dev` *(à remplacer par votre URL)* |
| Gestion de projet | *lien Trello / Notion à ajouter* |
| Documentation | dossier [`docs/`](docs) |

## Stack technique

| Couche | Technologie |
|---|---|
| Front-end | HTML5, CSS3, Bootstrap 5.3, JavaScript (ES6, sans framework), Chart.js |
| Back-end | PHP 8.3 natif (architecture MVC légère), PDO |
| Base relationnelle | MySQL 8 / MariaDB |
| Base NoSQL | MongoDB (statistiques) |
| Emails | PHPMailer (SMTP) |
| Déploiement | Docker, Fly.io |

## Installation en local

### Option 1 : avec Docker (recommandée)

Prérequis : [Docker Desktop](https://www.docker.com/products/docker-desktop/).

```bash
git clone https://github.com/valentinfigueiredo/studi-2026-2027-tp-developpeur-web-et-web-mobile.git
cd studi-2026-2027-tp-developpeur-web-et-web-mobile
docker compose up -d --build
docker compose exec app php bin/sync-mongo.php   # alimente MongoDB avec les commandes de démonstration
```

- Application : http://localhost:8000
- Boîte mail de test (Mailpit, tous les emails envoyés y arrivent) : http://localhost:8025

La base MySQL est créée et remplie automatiquement au premier démarrage (scripts du dossier `database/`).

### Option 2 : installation manuelle

Prérequis : PHP 8.2+ avec les extensions `pdo_mysql`, `mongodb`, `intl` et `curl`, Composer, MySQL 8 (ou MariaDB 10.6+), MongoDB 6+.

```bash
git clone https://github.com/valentinfigueiredo/studi-2026-2027-tp-developpeur-web-et-web-mobile.git
cd studi-2026-2027-tp-developpeur-web-et-web-mobile
composer install

# Base de données (création, données de démonstration, utilisateur applicatif)
mysql -u root -p < database/01_schema.sql
mysql -u root -p < database/02_data.sql
mysql -u root -p < database/03_droits.sql

# Configuration
cp .env.example .env        # puis adapter DB_PASSWORD (dev-password par défaut), MONGO_URI...

# Statistiques MongoDB
php bin/sync-mongo.php

# Serveur de développement
php -S localhost:8000 -t public public/index.php
```

Sans serveur SMTP configuré (`MAIL_HOST` vide), les emails sont enregistrés dans `storage/mails/` au lieu d'être envoyés.

## Comptes de démonstration

| Rôle | Email | Mot de passe |
|---|---|---|
| Administrateur | jose@vite-gourmand.fr | Admin@Vite2026! |
| Employée | julie@vite-gourmand.fr | Employe@Vite2026! |
| Utilisateur | marie.dupont@example.com | Client@Vite2026! |
| Utilisateur | thomas.martin@example.com | Client@Vite2026! |

## Structure du projet

```
├── public/               Seul dossier exposé sur le web
│   ├── index.php         Contrôleur frontal : routes, contrôle d'accès, CSRF
│   └── assets/           CSS, JavaScript, images
├── pages/                Contrôleurs (un fichier par page)
├── templates/            Vues HTML (layout, pages, partiels, emails)
├── src/                  Classes métier et techniques (namespace App)
├── database/             Scripts SQL : structure, données, droits
├── bin/sync-mongo.php    Reconstruction des statistiques MongoDB
├── docs/                 Documentation (conception, technique, projet, PDF)
├── docker/               Configuration Apache
├── Dockerfile            Image de production
├── docker-compose.yml    Environnement de développement complet
└── fly.toml              Configuration du déploiement Fly.io
```

## Déploiement

Le déploiement sur Fly.io est détaillé dans [`docs/04_deploiement.md`](docs/04_deploiement.md).

## Git : organisation des branches

- `master` : branche principale, version en production, toujours stable.
- `develop` : intégration des fonctionnalités terminées.
- `feature/<nom>` : une branche par fonctionnalité, créée depuis `develop`, fusionnée dans `develop` après tests.
- Une fois `develop` testée, elle est fusionnée dans `master`.

## Tests

`tests/e2e.py` rejoue 60 vérifications de bout en bout : parcours visiteur, client, employé et administrateur, ainsi que les protections de sécurité (CSRF, XSS, IDOR, force brute, upload malveillant...). Le mode opératoire est indiqué en tête du fichier.
