<?php

declare(strict_types=1);

use App\Database;

/**
 * Échappe une valeur avant de l'afficher dans le HTML.
 * C'est LA protection contre les failles XSS : à utiliser sur toute donnée dynamique.
 */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path = '/', array $params = []): string
{
    return $path . ($params ? '?' . http_build_query($params) : '');
}

function redirect(string $path, array $params = []): never
{
    header('Location: ' . url($path, $params));
    exit;
}

/** Messages "flash" : affichés une seule fois, après une redirection. */
function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function flashes(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

/** Affiche une page dans le gabarit général (header + footer). */
function render(string $template, array $vars = []): never
{
    $vars['contenu'] = render_partial('pages/' . $template, $vars);
    echo render_partial('layout', $vars);
    exit;
}

function render_partial(string $template, array $vars = []): string
{
    extract($vars, EXTR_SKIP);
    ob_start();
    require dirname(__DIR__) . '/templates/' . $template . '.php';
    return (string) ob_get_clean();
}

function abort(int $code, string $message = ''): never
{
    http_response_code($code);
    $titres = [403 => 'Accès refusé', 404 => 'Page introuvable', 400 => 'Requête invalide', 500 => 'Erreur serveur'];
    render('erreur', ['title' => $titres[$code] ?? 'Erreur', 'code' => $code, 'message' => $message]);
}

function json_response(mixed $data, int $code = 200): never
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function money(float|string|null $value): string
{
    return number_format((float) $value, 2, ',', ' ') . ' €';
}

function date_fr(?string $date, bool $avecHeure = false): string
{
    if (!$date) {
        return '';
    }
    $d = new DateTimeImmutable($date);
    return $avecHeure ? $d->format('d/m/Y à H\hi') : $d->format('d/m/Y');
}

function heure_fr(?string $time): string
{
    return $time ? (new DateTimeImmutable($time))->format('H\hi') : '';
}

/** Valeur précédemment saisie (pour ré-afficher un formulaire en erreur). */
function old(array $source, string $key, mixed $default = ''): string
{
    return e($source[$key] ?? $default);
}

/** Message d'erreur d'un champ + attributs ARIA pour les lecteurs d'écran (RGAA). */
function field_error(array $errors, string $field): string
{
    if (!isset($errors[$field])) {
        return '';
    }
    return '<div class="invalid-feedback d-block" id="' . e($field) . '-error">' . e($errors[$field]) . '</div>';
}

function aria_invalid(array $errors, string $field): string
{
    return isset($errors[$field]) ? ' is-invalid" aria-invalid="true" aria-describedby="' . e($field) . '-error' : '';
}

function current_path(): string
{
    return parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
}

/** Attributs d'un lien de navigation (page active signalée aux lecteurs d'écran). */
function nav_active(string $path, bool $exact = false): string
{
    $actif = current_path() === $path || (!$exact && $path !== '/' && str_starts_with(current_path(), $path . '/'));
    return $actif ? ' active" aria-current="page' : '';
}

function horaires(): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    try {
        return $cache = Database::run(
            "SELECT * FROM horaire ORDER BY FIELD(jour,'lundi','mardi','mercredi','jeudi','vendredi','samedi','dimanche')"
        )->fetchAll();
    } catch (\Throwable) {
        // Base indisponible : la page (y compris la page d'erreur) s'affiche quand même, sans horaires
        return [];
    }
}

function etoiles(int $note): string
{
    return str_repeat('★', $note) . str_repeat('☆', 5 - $note);
}

/**
 * Protection contre les "open redirect" : on n'accepte qu'un chemin interne au site
 * (sinon un lien piégé ?redirect=https://site-pirate.com renverrait vers un faux site après connexion).
 */
function safe_redirect_target(?string $target, string $default = '/'): string
{
    if (!is_string($target) || !str_starts_with($target, '/') || str_starts_with($target, '//') || str_contains($target, '\\')) {
        return $default;
    }
    return $target;
}

function accueil_selon_role(): string
{
    return match (App\Auth::user()['role'] ?? null) {
        App\Auth::ADMIN, App\Auth::EMPLOYE => '/employe',
        App\Auth::UTILISATEUR              => '/espace',
        default                            => '/',
    };
}
