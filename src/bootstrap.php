<?php

declare(strict_types=1);

/**
 * Initialisation commune à toutes les requêtes.
 */

use App\Env;

require dirname(__DIR__) . '/vendor/autoload.php';
require __DIR__ . '/helpers.php';

Env::load(dirname(__DIR__) . '/.env');
date_default_timezone_set('Europe/Paris');

// En production, aucune erreur technique n'est affichée à l'écran (elle révélerait
// des chemins, des requêtes SQL...) : elles sont seulement journalisées.
$debug = Env::bool('APP_DEBUG');
ini_set('display_errors', $debug ? '1' : '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

function is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'; // derrière un proxy HTTPS
}

if (PHP_SAPI !== 'cli') {
    // Cookie de session : inaccessible au JavaScript (HttpOnly), envoyé uniquement en HTTPS (Secure),
    // et pas envoyé par les requêtes venant d'autres sites (SameSite=Lax, complète la protection CSRF).
    session_name('VG_SESSION');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_start();

    // En-têtes de sécurité HTTP (et suppression de celui qui révèle la version de PHP)
    header_remove('X-Powered-By');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), camera=(), microphone=()');
    // CSP : le navigateur n'exécute que les scripts de notre site et du CDN Bootstrap/Chart.js.
    // Aucun script "inline" n'est autorisé : une injection XSS ne pourrait donc pas s'exécuter.
    header("Content-Security-Policy: default-src 'self'; "
        . "script-src 'self' https://cdn.jsdelivr.net; "
        . "style-src 'self' https://cdn.jsdelivr.net https://fonts.googleapis.com; "
        . "font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net; "
        . "img-src 'self' data:; "
        . "connect-src 'self' https://api-adresse.data.gouv.fr; "
        . "frame-ancestors 'none'; form-action 'self'; base-uri 'self'");
    if (is_https()) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}
