<?php

declare(strict_types=1);

use App\Stats;

// Données lues dans MongoDB (base non relationnelle), comme l'exige le cahier des charges
$debut = $_GET['debut'] ?? '';
$fin = $_GET['fin'] ?? '';
$menu = filter_var($_GET['menu'] ?? '', FILTER_VALIDATE_INT) ?: null;

$dateValide = static fn (string $d) => $d === '' || (bool) DateTimeImmutable::createFromFormat('!Y-m-d', $d);
if (!$dateValide($debut) || !$dateValide($fin)) {
    json_response(['erreur' => 'Dates invalides.'], 400);
}

try {
    $lignes = Stats::parMenu($debut ?: null, $fin ?: null, $menu);
} catch (Throwable $e) {
    error_log('[Stats] ' . $e->getMessage());
    json_response(['erreur' => 'La base de statistiques est momentanément indisponible.'], 503);
}

json_response([
    'menus'            => $lignes,
    'total_commandes'  => array_sum(array_column($lignes, 'nb_commandes')),
    'chiffre_affaires' => round(array_sum(array_column($lignes, 'chiffre_affaires')), 2),
]);
