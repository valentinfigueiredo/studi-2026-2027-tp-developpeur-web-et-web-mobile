<?php

declare(strict_types=1);

namespace App;

/**
 * Protection CSRF (Cross-Site Request Forgery).
 *
 * Un jeton aléatoire est stocké en session et ajouté en champ caché dans chaque formulaire.
 * Le routeur refuse toute requête POST dont le jeton ne correspond pas : un site malveillant
 * ne peut donc pas faire soumettre un formulaire à la place de l'utilisateur connecté.
 */
final class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . e(self::token()) . '">';
    }

    public static function check(?string $token): bool
    {
        // hash_equals : comparaison en temps constant (évite les attaques temporelles)
        return is_string($token) && !empty($_SESSION['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], $token);
    }
}
