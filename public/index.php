<?php

declare(strict_types=1);

/**
 * Contrôleur frontal : TOUTES les requêtes passent par ce fichier.
 *
 * Avantages :
 *  - un seul point d'entrée, donc un seul endroit pour les contrôles de sécurité
 *    (jeton CSRF, rôles), impossibles à oublier sur une page ;
 *  - seul le dossier public/ est exposé sur le web : le code (src/, pages/),
 *    le .env et les scripts SQL ne sont pas accessibles par une URL.
 */

use App\Auth;
use App\Csrf;

// Serveur PHP intégré (développement) : les fichiers statiques sont servis directement
if (PHP_SAPI === 'cli-server' && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}

require dirname(__DIR__) . '/src/bootstrap.php';

const PUBLIC_ = null;
const CONNECTE = [Auth::UTILISATEUR, Auth::EMPLOYE, Auth::ADMIN];
const CLIENT = [Auth::UTILISATEUR];
const STAFF = [Auth::EMPLOYE, Auth::ADMIN];
const ADMIN = [Auth::ADMIN];

// URL => [fichier dans pages/, rôles autorisés (null = tout le monde)]
$routes = [
    '/'                        => ['accueil', PUBLIC_],
    '/menus'                   => ['menus', PUBLIC_],
    '/menu'                    => ['menu', PUBLIC_],
    '/contact'                 => ['contact', PUBLIC_],
    '/mentions-legales'        => ['mentions-legales', PUBLIC_],
    '/cgv'                     => ['cgv', PUBLIC_],
    '/inscription'             => ['auth/inscription', PUBLIC_],
    '/connexion'               => ['auth/connexion', PUBLIC_],
    '/deconnexion'             => ['auth/deconnexion', CONNECTE],
    '/mot-de-passe-oublie'     => ['auth/mot-de-passe-oublie', PUBLIC_],
    '/reinitialiser'           => ['auth/reinitialiser', PUBLIC_],
    '/api/menus'               => ['api/menus', PUBLIC_],
    '/commander'               => ['commande/commander', CLIENT],
    '/espace'                  => ['espace/commandes', CLIENT],
    '/espace/commande'         => ['espace/commande', CLIENT],
    '/espace/commande/modifier'=> ['espace/modifier', CLIENT],
    '/espace/avis'             => ['espace/avis', CLIENT],
    '/espace/profil'           => ['espace/profil', CONNECTE],
    '/employe'                 => ['employe/commandes', STAFF],
    '/employe/commande'        => ['employe/commande', STAFF],
    '/employe/menus'           => ['employe/menus', STAFF],
    '/employe/menu'            => ['employe/menu-form', STAFF],
    '/employe/plats'           => ['employe/plats', STAFF],
    '/employe/horaires'        => ['employe/horaires', STAFF],
    '/employe/avis'            => ['employe/avis', STAFF],
    '/admin/employes'          => ['admin/employes', ADMIN],
    '/admin/statistiques'      => ['admin/statistiques', ADMIN],
    '/api/statistiques'        => ['api/statistiques', ADMIN],
];

$path = rtrim(current_path(), '/') ?: '/';

try {
    if (!isset($routes[$path])) {
        abort(404);
    }
    [$page, $roles] = $routes[$path];

    Auth::refresh();

    // Contrôle d'accès centralisé
    if ($roles !== null) {
        if (!Auth::check()) {
            flash('info', 'Connectez-vous pour accéder à cette page.');
            redirect('/connexion', ['redirect' => $_SERVER['REQUEST_URI']]);
        }
        if (!Auth::hasRole(...$roles)) {
            abort(403, 'Vous n\'avez pas les droits nécessaires pour accéder à cette page.');
        }
    }

    // Protection CSRF centralisée sur tous les formulaires
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !Csrf::check($_POST['csrf_token'] ?? null)) {
        abort(400, 'Le formulaire a expiré. Rechargez la page et réessayez.');
    }

    require dirname(__DIR__) . '/pages/' . $page . '.php';
} catch (\Throwable $e) {
    error_log((string) $e);
    if (App\Env::bool('APP_DEBUG')) {
        throw $e;
    }
    abort(500, 'Une erreur inattendue est survenue. Veuillez réessayer plus tard.');
}
