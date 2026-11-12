<?php

declare(strict_types=1);

namespace App;

use PDO;

/**
 * Connexion unique à MySQL via PDO (patron Singleton).
 *
 * Choix de sécurité :
 *  - ERRMODE_EXCEPTION : une erreur SQL lève une exception au lieu d'échouer en silence.
 *  - EMULATE_PREPARES = false : vraies requêtes préparées côté serveur MySQL,
 *    la requête et les données voyagent séparément => protection contre l'injection SQL.
 */
final class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                Env::get('DB_HOST', '127.0.0.1'),
                Env::get('DB_PORT', '3306'),
                Env::get('DB_NAME', 'vite_gourmand')
            );
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            // Connexion chiffrée (TLS) si l'hébergeur de la base l'exige
            $ca = Env::get('DB_SSL_CA');
            if ($ca !== '') {
                $options[PDO::MYSQL_ATTR_SSL_CA] = $ca;
            }
            self::$pdo = new PDO($dsn, Env::get('DB_USER', 'root'), Env::get('DB_PASSWORD'), $options);
            // MySQL et PHP sur le même fuseau horaire (Europe/Paris, heure d'été comprise)
            self::$pdo->exec("SET time_zone = '" . (new \DateTimeImmutable())->format('P') . "'");
        }
        return self::$pdo;
    }

    /** Raccourci : exécute une requête préparée et renvoie le PDOStatement. */
    public static function run(string $sql, array $params = []): \PDOStatement
    {
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}
