<?php

declare(strict_types=1);

namespace App;

/**
 * Authentification et contrôle d'accès par rôle.
 */
final class Auth
{
    public const UTILISATEUR = 'utilisateur';
    public const EMPLOYE = 'employe';
    public const ADMIN = 'administrateur';

    /** bcrypt coût 12 : environ 250 ms par hash, très lent pour une attaque par force brute. */
    public const HASH_OPTIONS = ['cost' => 12];

    private const MAX_TENTATIVES = 5;
    private const FENETRE_MINUTES = 15;

    /**
     * Vérifie email + mot de passe.
     * Renvoie l'utilisateur, ou un message d'erreur générique (on ne dit jamais
     * si c'est l'email ou le mot de passe qui est faux : ça aiderait un attaquant).
     */
    public static function attempt(string $email, string $password): array|string
    {
        $email = mb_strtolower(trim($email));

        if (self::isLocked($email)) {
            return 'Trop de tentatives de connexion. Réessayez dans ' . self::FENETRE_MINUTES . ' minutes.';
        }

        $user = Database::run(
            'SELECT u.utilisateur_id, u.email, u.password, u.prenom, u.nom, u.actif, r.libelle AS role
             FROM utilisateur u JOIN role r ON r.role_id = u.role_id
             WHERE u.email = :email',
            ['email' => $email]
        )->fetch();

        // Si l'utilisateur n'existe pas, on vérifie quand même un hash factice :
        // le temps de réponse est identique, on ne peut pas deviner les emails inscrits.
        $hash = $user['password'] ?? '$2y$12$bE/nMxuyxiJkPGEO4YFVuel3krC20rKrm8nHfjL15just4a.ea6wu';
        $valid = password_verify($password, $hash);

        if (!$user || !$valid) {
            Database::run(
                'INSERT INTO tentative_connexion (email, ip) VALUES (:email, :ip)',
                ['email' => $email, 'ip' => $_SERVER['REMOTE_ADDR'] ?? 'cli']
            );
            return 'Identifiants incorrects.';
        }

        if (!$user['actif']) {
            return 'Ce compte a été désactivé. Contactez l\'administrateur.';
        }

        // Si l'algorithme ou le coût de hachage a évolué, on met à jour le hash
        if (password_needs_rehash($user['password'], PASSWORD_BCRYPT, self::HASH_OPTIONS)) {
            Database::run(
                'UPDATE utilisateur SET password = :p WHERE utilisateur_id = :id',
                ['p' => self::hash($password), 'id' => $user['utilisateur_id']]
            );
        }

        Database::run('DELETE FROM tentative_connexion WHERE email = :email', ['email' => $email]);
        unset($user['password']);
        return $user;
    }

    private static function isLocked(string $email): bool
    {
        $count = (int) Database::run(
            'SELECT COUNT(*) FROM tentative_connexion
             WHERE email = :email AND date_tentative > (NOW() - INTERVAL ' . self::FENETRE_MINUTES . ' MINUTE)',
            ['email' => $email]
        )->fetchColumn();
        return $count >= self::MAX_TENTATIVES;
    }

    public static function hash(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, self::HASH_OPTIONS);
    }

    public static function login(array $user): void
    {
        // Nouvel identifiant de session après connexion : empêche la fixation de session
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'     => (int) $user['utilisateur_id'],
            'email'  => $user['email'],
            'prenom' => $user['prenom'],
            'nom'    => $user['nom'],
            'role'   => $user['role'],
        ];
    }

    public static function logout(): void
    {
        $_SESSION = [];
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        session_destroy();
    }

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function id(): ?int
    {
        return self::user()['id'] ?? null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function hasRole(string ...$roles): bool
    {
        $user = self::user();
        return $user !== null && in_array($user['role'], $roles, true);
    }

    /** Raccourci : employé OU administrateur (l'admin peut tout ce que fait l'employé). */
    public static function isStaff(): bool
    {
        return self::hasRole(self::EMPLOYE, self::ADMIN);
    }

    /**
     * Vérifie à chaque requête que le compte est toujours actif :
     * si l'admin désactive un employé, sa session en cours est coupée immédiatement.
     */
    public static function refresh(): void
    {
        if (!self::check()) {
            return;
        }
        $row = Database::run(
            'SELECT u.actif, r.libelle AS role FROM utilisateur u JOIN role r ON r.role_id = u.role_id
             WHERE u.utilisateur_id = :id',
            ['id' => self::id()]
        )->fetch();
        if (!$row || !$row['actif']) {
            self::logout();
            session_start();
            flash('warning', 'Votre session a été fermée.');
            return;
        }
        $_SESSION['user']['role'] = $row['role'];
    }
}
