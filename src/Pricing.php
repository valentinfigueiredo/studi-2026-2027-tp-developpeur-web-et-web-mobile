<?php

declare(strict_types=1);

namespace App;

/**
 * Règles de calcul du prix d'une commande (RG7, RG8, RG9).
 * Le même calcul est fait en JavaScript pour l'affichage en direct,
 * mais c'est TOUJOURS ce calcul serveur qui est enregistré (on ne fait jamais confiance au navigateur).
 */
final class Pricing
{
    public const FRAIS_LIVRAISON_BASE = 5.00;  // € hors Bordeaux
    public const PRIX_PAR_KM = 0.59;           // € par km parcouru
    public const TAUX_REDUCTION = 0.10;        // 10 %
    public const SEUIL_REDUCTION = 5;          // personnes au-delà du minimum

    /**
     * @param array $menu doit contenir prix_par_personne et nombre_personne_minimum
     */
    public static function calculer(array $menu, int $nombrePersonne, string $ville, float $distanceKm): array
    {
        $min = (int) $menu['nombre_personne_minimum'];
        $prixParPersonne = (float) $menu['prix_par_personne'];

        $prixMenu = round($prixParPersonne * $nombrePersonne, 2);

        $reduction = $nombrePersonne >= $min + self::SEUIL_REDUCTION
            ? round($prixMenu * self::TAUX_REDUCTION, 2)
            : 0.0;

        $livraison = self::estBordeaux($ville)
            ? 0.0
            : round(self::FRAIS_LIVRAISON_BASE + self::PRIX_PAR_KM * $distanceKm, 2);

        return [
            'prix_menu'         => $prixMenu,
            'montant_reduction' => $reduction,
            'prix_livraison'    => $livraison,
            'prix_total'        => round($prixMenu - $reduction + $livraison, 2),
        ];
    }

    /** « Bordeaux », « bordeaux », « BORDEAUX » ou « Bordeaux Cedex » sont acceptés. */
    public static function estBordeaux(string $ville): bool
    {
        $v = mb_strtolower(trim($ville));
        $v = preg_replace('/\s+cedex.*$/u', '', $v);
        return $v === 'bordeaux';
    }
}
