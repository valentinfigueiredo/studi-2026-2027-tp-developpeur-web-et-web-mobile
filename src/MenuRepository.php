<?php

declare(strict_types=1);

namespace App;

/**
 * Accès aux données des menus (requêtes SQL regroupées au même endroit).
 */
final class MenuRepository
{
    /**
     * Vue globale filtrée. Chaque filtre ajoute une condition AVEC un paramètre lié :
     * la requête reste dynamique sans jamais concaténer une saisie utilisateur dans le SQL.
     */
    public static function rechercher(array $f): array
    {
        $where = ['m.actif = 1'];
        $params = [];

        if (isset($f['prix_max']) && $f['prix_max'] !== '') {
            $where[] = 'm.prix_minimum <= :prix_max';
            $params['prix_max'] = (float) $f['prix_max'];
        }
        if (isset($f['prix_min']) && $f['prix_min'] !== '') {
            $where[] = 'm.prix_minimum >= :prix_min';
            $params['prix_min'] = (float) $f['prix_min'];
        }
        if (!empty($f['theme'])) {
            $where[] = 'm.theme_id = :theme';
            $params['theme'] = (int) $f['theme'];
        }
        if (!empty($f['regime'])) {
            $where[] = 'm.regime_id = :regime';
            $params['regime'] = (int) $f['regime'];
        }
        // « Nombre de personnes minimum » : menus commandables pour ce nombre de convives,
        // c'est-à-dire dont le minimum requis est inférieur ou égal au nombre saisi.
        if (!empty($f['personnes'])) {
            $where[] = 'm.nombre_personne_minimum <= :personnes';
            $params['personnes'] = (int) $f['personnes'];
        }

        $sql = 'SELECT m.menu_id, m.titre, m.description, m.nombre_personne_minimum, m.prix_minimum,
                       m.quantite_restante, t.libelle AS theme, r.libelle AS regime,
                       (SELECT chemin FROM menu_image i WHERE i.menu_id = m.menu_id ORDER BY ordre LIMIT 1) AS image,
                       (SELECT texte_alt FROM menu_image i WHERE i.menu_id = m.menu_id ORDER BY ordre LIMIT 1) AS image_alt
                FROM menu m
                JOIN theme t ON t.theme_id = m.theme_id
                JOIN regime r ON r.regime_id = m.regime_id
                WHERE ' . implode(' AND ', $where) . '
                ORDER BY m.prix_minimum';

        return Database::run($sql, $params)->fetchAll();
    }

    public static function trouver(int $id, bool $inclureInactifs = false): ?array
    {
        $menu = Database::run(
            'SELECT m.*, t.libelle AS theme, r.libelle AS regime
             FROM menu m
             JOIN theme t ON t.theme_id = m.theme_id
             JOIN regime r ON r.regime_id = m.regime_id
             WHERE m.menu_id = :id' . ($inclureInactifs ? '' : ' AND m.actif = 1'),
            ['id' => $id]
        )->fetch();

        if (!$menu) {
            return null;
        }

        $menu['images'] = Database::run(
            'SELECT image_id, chemin, texte_alt FROM menu_image WHERE menu_id = :id ORDER BY ordre',
            ['id' => $id]
        )->fetchAll();

        // Plats du menu avec leurs allergènes regroupés (GROUP_CONCAT évite une requête par plat)
        $menu['plats'] = Database::run(
            "SELECT p.plat_id, p.nom, p.type_plat, p.description,
                    GROUP_CONCAT(a.libelle ORDER BY a.libelle SEPARATOR ', ') AS allergenes
             FROM menu_plat mp
             JOIN plat p ON p.plat_id = mp.plat_id
             LEFT JOIN plat_allergene pa ON pa.plat_id = p.plat_id
             LEFT JOIN allergene a ON a.allergene_id = pa.allergene_id
             WHERE mp.menu_id = :id
             GROUP BY p.plat_id
             ORDER BY FIELD(p.type_plat, 'entree', 'plat', 'dessert'), p.nom",
            ['id' => $id]
        )->fetchAll();

        return $menu;
    }

    public static function themes(): array
    {
        return Database::run('SELECT theme_id, libelle FROM theme ORDER BY libelle')->fetchAll();
    }

    public static function regimes(): array
    {
        return Database::run('SELECT regime_id, libelle FROM regime ORDER BY libelle')->fetchAll();
    }

    public static function commandables(): array
    {
        return Database::run(
            'SELECT menu_id, titre, nombre_personne_minimum, prix_minimum, quantite_restante
             FROM menu WHERE actif = 1 AND quantite_restante > 0 ORDER BY titre'
        )->fetchAll();
    }
}
