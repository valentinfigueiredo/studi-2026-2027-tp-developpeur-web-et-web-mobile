<?php

declare(strict_types=1);

namespace App;

/**
 * Calcul de la distance de livraison depuis Bordeaux.
 *
 * Géocodage via l'API Adresse du gouvernement (api-adresse.data.gouv.fr) :
 * gratuite, sans clé, données officielles de la Base Adresse Nationale.
 * Distance calculée à vol d'oiseau (formule de Haversine), arrondie au km supérieur.
 * Limite connue : la distance routière réelle est un peu plus longue (amélioration possible
 * avec une API d'itinéraire comme OSRM).
 */
final class Geo
{
    // Point de départ : centre de Bordeaux (place de la Comédie)
    private const DEPART_LAT = 44.8425;
    private const DEPART_LON = -0.5745;

    /** @return array{lat: float, lon: float}|null */
    public static function geocoder(string $adresse, string $codePostal, string $ville): ?array
    {
        $query = http_build_query(['q' => "$adresse $codePostal $ville", 'limit' => 1]);
        $ch = curl_init('https://api-adresse.data.gouv.fr/search/?' . $query);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 4,
            CURLOPT_CONNECTTIMEOUT => 3,
        ]);
        $json = curl_exec($ch);
        curl_close($ch);
        if (!is_string($json)) {
            return null;
        }
        $data = json_decode($json, true);
        $coords = $data['features'][0]['geometry']['coordinates'] ?? null;
        if (!is_array($coords)) {
            return null;
        }
        return ['lon' => (float) $coords[0], 'lat' => (float) $coords[1]];
    }

    public static function distanceDepuisBordeaux(float $lat, float $lon): float
    {
        $r = 6371; // rayon de la Terre en km
        $dLat = deg2rad($lat - self::DEPART_LAT);
        $dLon = deg2rad($lon - self::DEPART_LON);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad(self::DEPART_LAT)) * cos(deg2rad($lat)) * sin($dLon / 2) ** 2;
        return ceil($r * 2 * atan2(sqrt($a), sqrt(1 - $a)));
    }

    /**
     * Distance en km à facturer. 0 pour Bordeaux.
     * Si l'API est injoignable, on utilise les coordonnées transmises par le navigateur
     * (obtenues par l'autocomplétion), après contrôle qu'elles sont bien en France métropolitaine.
     * Renvoie null si aucune position fiable n'est disponible.
     */
    public static function distanceLivraison(string $adresse, string $cp, string $ville, mixed $latClient, mixed $lonClient): ?float
    {
        if (Pricing::estBordeaux($ville)) {
            return 0.0;
        }
        $pos = self::geocoder($adresse, $cp, $ville);
        if ($pos === null) {
            $lat = filter_var($latClient, FILTER_VALIDATE_FLOAT);
            $lon = filter_var($lonClient, FILTER_VALIDATE_FLOAT);
            if ($lat === false || $lon === false || $lat < 41 || $lat > 51.5 || $lon < -5.5 || $lon > 10) {
                return null;
            }
            $pos = ['lat' => $lat, 'lon' => $lon];
        }
        return self::distanceDepuisBordeaux($pos['lat'], $pos['lon']);
    }
}
