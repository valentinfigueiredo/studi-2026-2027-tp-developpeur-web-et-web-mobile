<?php

declare(strict_types=1);

namespace App;

use MongoDB\BSON\UTCDateTime;
use MongoDB\Client;
use MongoDB\Collection;

/**
 * Statistiques de commandes stockées dans MongoDB (base non relationnelle).
 *
 * Chaque commande MySQL a un document "miroir" dénormalisé dans la collection commandes_stats.
 * L'administrateur interroge cette collection pour ses graphiques : aucune jointure,
 * et les requêtes d'analyse ne chargent pas la base transactionnelle.
 *
 * Si MongoDB est indisponible, les commandes continuent de fonctionner (on journalise l'erreur) :
 * les statistiques peuvent être reconstruites avec `php bin/sync-mongo.php`.
 */
final class Stats
{
    private static ?Collection $collection = null;

    public static function collection(): Collection
    {
        if (self::$collection === null) {
            $client = new Client(Env::get('MONGO_URI', 'mongodb://127.0.0.1:27017'), [], [
                'typeMap' => ['root' => 'array', 'document' => 'array', 'array' => 'array'],
            ]);
            self::$collection = $client
                ->selectDatabase(Env::get('MONGO_DB', 'vite_gourmand'))
                ->selectCollection('commandes_stats');
        }
        return self::$collection;
    }

    /** Crée ou met à jour le document statistique d'une commande (upsert). */
    public static function synchroniser(int $commandeId): void
    {
        try {
            $c = Database::run(
                'SELECT c.commande_id, c.menu_id, m.titre AS menu_titre, c.date_commande,
                        c.nombre_personne, c.prix_total, c.statut
                 FROM commande c JOIN menu m ON m.menu_id = c.menu_id
                 WHERE c.commande_id = :id',
                ['id' => $commandeId]
            )->fetch();
            if (!$c) {
                return;
            }
            self::collection()->updateOne(
                ['commande_id' => (int) $c['commande_id']],
                ['$set' => [
                    'commande_id'     => (int) $c['commande_id'],
                    'menu_id'         => (int) $c['menu_id'],
                    'menu_titre'      => $c['menu_titre'],
                    'date_commande'   => new UTCDateTime(strtotime($c['date_commande']) * 1000),
                    'nombre_personne' => (int) $c['nombre_personne'],
                    'prix_total'      => (float) $c['prix_total'],
                    'statut'          => $c['statut'],
                ]],
                ['upsert' => true]
            );
        } catch (\Throwable $e) {
            error_log('[Stats] synchronisation impossible : ' . $e->getMessage());
        }
    }

    /**
     * Nombre de commandes et chiffre d'affaires par menu (hors commandes annulées).
     * Filtres facultatifs : période (dates Y-m-d) et menu.
     */
    public static function parMenu(?string $debut, ?string $fin, ?int $menuId): array
    {
        $match = ['statut' => ['$ne' => 'annulee']];
        if ($menuId) {
            $match['menu_id'] = $menuId;
        }
        if ($debut || $fin) {
            $match['date_commande'] = [];
            if ($debut) {
                $match['date_commande']['$gte'] = new UTCDateTime(strtotime($debut . ' 00:00:00') * 1000);
            }
            if ($fin) {
                $match['date_commande']['$lte'] = new UTCDateTime(strtotime($fin . ' 23:59:59') * 1000);
            }
        }

        $cursor = self::collection()->aggregate([
            ['$match' => $match],
            ['$group' => [
                '_id'              => ['menu_id' => '$menu_id', 'menu_titre' => '$menu_titre'],
                'nb_commandes'     => ['$sum' => 1],
                'chiffre_affaires' => ['$sum' => '$prix_total'],
            ]],
            ['$sort' => ['nb_commandes' => -1]],
        ]);

        $resultats = [];
        foreach ($cursor as $doc) {
            $resultats[] = [
                'menu_id'          => $doc['_id']['menu_id'],
                'menu_titre'       => $doc['_id']['menu_titre'],
                'nb_commandes'     => $doc['nb_commandes'],
                'chiffre_affaires' => round($doc['chiffre_affaires'], 2),
            ];
        }
        return $resultats;
    }
}
