<?php

declare(strict_types=1);

/**
 * Reconstruit la collection MongoDB des statistiques à partir de MySQL.
 * À lancer après l'import du jeu de données, ou si MongoDB a été indisponible :
 *     php bin/sync-mongo.php
 */

use App\Database;
use App\Stats;

require dirname(__DIR__) . '/src/bootstrap.php';

$ids = Database::run('SELECT commande_id FROM commande')->fetchAll(PDO::FETCH_COLUMN);
Stats::collection()->deleteMany([]);
foreach ($ids as $id) {
    Stats::synchroniser((int) $id);
}
echo count($ids) . " commande(s) synchronisée(s) dans MongoDB.\n";
