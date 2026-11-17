<?php

declare(strict_types=1);

namespace App;

/**
 * Lecture de la configuration depuis les variables d'environnement.
 * En local, elles sont chargées depuis le fichier .env (jamais versionné : il contient des secrets).
 * En production (Alwaysdata), le fichier .env est créé directement sur le serveur, hors du dossier public/.
 */
final class Env
{
    public static function load(string $path): void
    {
        if (!is_file($path)) {
            return;
        }
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = array_map('trim', explode('=', $line, 2));
            $value = trim($value, "\"'");
            // Une variable déjà définie par le système est prioritaire sur le fichier .env
            if (getenv($key) === false) {
                putenv("$key=$value");
                $_ENV[$key] = $value;
            }
        }
    }

    public static function get(string $key, string $default = ''): string
    {
        $value = getenv($key);
        return $value === false ? $default : $value;
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $value = getenv($key);
        return $value === false ? $default : filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
