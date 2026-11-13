<?php

declare(strict_types=1);

namespace App;

/**
 * Règles métier des commandes : création, modification, annulation, changement de statut.
 * Toutes les opérations qui touchent plusieurs tables sont faites dans une TRANSACTION :
 * soit tout réussit, soit rien n'est enregistré (pas de stock décrémenté sans commande, par exemple).
 */
final class CommandeService
{
    public const STATUTS = [
        'en_attente'                 => 'En attente de validation',
        'accepte'                    => 'Acceptée',
        'en_preparation'             => 'En préparation',
        'en_cours_de_livraison'      => 'En cours de livraison',
        'livre'                      => 'Livrée',
        'en_attente_retour_materiel' => 'En attente du retour de matériel',
        'terminee'                   => 'Terminée',
        'annulee'                    => 'Annulée',
    ];

    /** Transitions de statut autorisées pour un employé (cycle de vie de la commande). */
    public const TRANSITIONS = [
        'en_attente'                 => ['accepte'],
        'accepte'                    => ['en_preparation'],
        'en_preparation'             => ['en_cours_de_livraison'],
        'en_cours_de_livraison'      => ['livre'],
        'livre'                      => ['terminee', 'en_attente_retour_materiel'],
        'en_attente_retour_materiel' => ['terminee'],
        'terminee'                   => [],
        'annulee'                    => [],
    ];

    /**
     * Valide la saisie du formulaire de commande et calcule le prix côté serveur.
     * @return array{0: array, 1: array} [erreurs, données nettoyées]
     */
    public static function valider(array $in, array $menu): array
    {
        $v = new Validator();
        $data = [
            'date_prestation'    => trim($in['date_prestation'] ?? ''),
            'heure_livraison'    => trim($in['heure_livraison'] ?? ''),
            'adresse_prestation' => trim($in['adresse_prestation'] ?? ''),
            'code_postal'        => trim($in['code_postal'] ?? ''),
            'ville_prestation'   => trim($in['ville_prestation'] ?? ''),
            'nombre_personne'    => (int) ($in['nombre_personne'] ?? 0),
            'pret_materiel'      => !empty($in['pret_materiel']),
        ];

        $v->required('date_prestation', $data['date_prestation'], 'Date de la prestation')
          ->required('heure_livraison', $data['heure_livraison'], 'Heure de livraison')
          ->required('adresse_prestation', $data['adresse_prestation'], 'Adresse')
          ->required('code_postal', $data['code_postal'], 'Code postal')
          ->required('ville_prestation', $data['ville_prestation'], 'Ville')
          ->maxLength('adresse_prestation', $data['adresse_prestation'], 255, 'Adresse')
          ->maxLength('ville_prestation', $data['ville_prestation'], 100, 'Ville')
          ->custom('code_postal', (bool) preg_match('/^\d{5}$/', $data['code_postal']), 'Le code postal doit contenir 5 chiffres.')
          ->intRange(
              'nombre_personne',
              $data['nombre_personne'],
              (int) $menu['nombre_personne_minimum'],
              500,
              'Ce menu se commande pour ' . $menu['nombre_personne_minimum'] . ' personnes minimum.'
          );

        if ($data['date_prestation'] !== '') {
            $v->date('date_prestation', $data['date_prestation'], 'Date de la prestation');
            $v->custom(
                'date_prestation',
                $data['date_prestation'] > date('Y-m-d'),
                'La date de la prestation doit être postérieure à aujourd\'hui.'
            );
        }
        if ($data['heure_livraison'] !== '') {
            $v->time('heure_livraison', $data['heure_livraison'], 'Heure de livraison');
        }

        if ($v->fails()) {
            return [$v->errors(), $data];
        }

        $distance = Geo::distanceLivraison(
            $data['adresse_prestation'],
            $data['code_postal'],
            $data['ville_prestation'],
            $in['latitude'] ?? null,
            $in['longitude'] ?? null
        );
        if ($distance === null) {
            return [['adresse_prestation' => 'Adresse introuvable : sélectionnez-la dans la liste de suggestions.'], $data];
        }

        $data['distance_km'] = $distance;
        $data += Pricing::calculer($menu, $data['nombre_personne'], $data['ville_prestation'], $distance);
        return [[], $data];
    }

    public static function creer(int $utilisateurId, int $menuId, array $data): int
    {
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            // FOR UPDATE verrouille la ligne du menu : deux clients ne peuvent pas
            // prendre la dernière place en stock au même moment.
            $stock = Database::run(
                'SELECT quantite_restante FROM menu WHERE menu_id = :id AND actif = 1 FOR UPDATE',
                ['id' => $menuId]
            )->fetchColumn();
            if ($stock === false || (int) $stock < 1) {
                throw new \DomainException('Ce menu n\'est plus disponible à la commande.');
            }

            Database::run(
                'INSERT INTO commande (numero_commande, utilisateur_id, menu_id, date_prestation, heure_livraison,
                    adresse_prestation, code_postal, ville_prestation, distance_km, nombre_personne,
                    prix_menu, montant_reduction, prix_livraison, prix_total, pret_materiel)
                 VALUES (:numero, :user, :menu, :date, :heure, :adresse, :cp, :ville, :distance, :nb,
                    :prix_menu, :reduction, :livraison, :total, :materiel)',
                [
                    'numero'    => 'TMP-' . bin2hex(random_bytes(6)),
                    'user'      => $utilisateurId,
                    'menu'      => $menuId,
                    'date'      => $data['date_prestation'],
                    'heure'     => $data['heure_livraison'],
                    'adresse'   => $data['adresse_prestation'],
                    'cp'        => $data['code_postal'],
                    'ville'     => $data['ville_prestation'],
                    'distance'  => $data['distance_km'],
                    'nb'        => $data['nombre_personne'],
                    'prix_menu' => $data['prix_menu'],
                    'reduction' => $data['montant_reduction'],
                    'livraison' => $data['prix_livraison'],
                    'total'     => $data['prix_total'],
                    'materiel'  => (int) $data['pret_materiel'],
                ]
            );
            $id = (int) $pdo->lastInsertId();
            Database::run(
                'UPDATE commande SET numero_commande = :numero WHERE commande_id = :id',
                ['numero' => sprintf('CMD-%s-%05d', date('Y'), $id), 'id' => $id]
            );
            Database::run('UPDATE menu SET quantite_restante = quantite_restante - 1 WHERE menu_id = :id', ['id' => $menuId]);
            Database::run('INSERT INTO suivi_commande (commande_id, statut) VALUES (:id, :statut)', ['id' => $id, 'statut' => 'en_attente']);
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        Stats::synchroniser($id);
        $commande = self::trouver($id);
        Mailer::send($commande['email'], 'Confirmation de votre commande ' . $commande['numero_commande'], 'commande-confirmation', ['c' => $commande]);
        return $id;
    }

    /** Commande + client + menu. Si $utilisateurId est fourni, vérifie qu'elle lui appartient. */
    public static function trouver(int $id, ?int $utilisateurId = null): ?array
    {
        $sql = 'SELECT c.*, m.titre AS menu_titre, m.conditions AS menu_conditions,
                       m.nombre_personne_minimum, m.prix_par_personne,
                       m.prix_par_personne * m.nombre_personne_minimum AS prix_minimum,
                       u.prenom, u.nom, u.email, u.telephone,
                       a.avis_id, a.note AS avis_note, a.statut AS avis_statut
                FROM commande c
                JOIN menu m ON m.menu_id = c.menu_id
                JOIN utilisateur u ON u.utilisateur_id = c.utilisateur_id
                LEFT JOIN avis a ON a.commande_id = c.commande_id
                WHERE c.commande_id = :id';
        $params = ['id' => $id];
        // Contrôle d'appartenance : empêche un client de lire la commande d'un autre
        // en changeant simplement l'identifiant dans l'URL (faille IDOR).
        if ($utilisateurId !== null) {
            $sql .= ' AND c.utilisateur_id = :user';
            $params['user'] = $utilisateurId;
        }
        $commande = Database::run($sql, $params)->fetch();
        return $commande ?: null;
    }

    public static function suivi(int $commandeId): array
    {
        return Database::run(
            'SELECT statut, date_modification FROM suivi_commande WHERE commande_id = :id ORDER BY date_modification, suivi_id',
            ['id' => $commandeId]
        )->fetchAll();
    }

    /** RG11 : le client modifie tout sauf le menu, tant que la commande est « en attente ». */
    public static function modifierParClient(array $commande, array $data): void
    {
        if ($commande['statut'] !== 'en_attente') {
            throw new \DomainException('Cette commande a déjà été acceptée : elle ne peut plus être modifiée.');
        }
        Database::run(
            'UPDATE commande SET date_prestation = :date, heure_livraison = :heure, adresse_prestation = :adresse,
                code_postal = :cp, ville_prestation = :ville, distance_km = :distance, nombre_personne = :nb,
                prix_menu = :prix_menu, montant_reduction = :reduction, prix_livraison = :livraison,
                prix_total = :total, pret_materiel = :materiel
             WHERE commande_id = :id AND statut = \'en_attente\'',
            [
                'date'      => $data['date_prestation'],
                'heure'     => $data['heure_livraison'],
                'adresse'   => $data['adresse_prestation'],
                'cp'        => $data['code_postal'],
                'ville'     => $data['ville_prestation'],
                'distance'  => $data['distance_km'],
                'nb'        => $data['nombre_personne'],
                'prix_menu' => $data['prix_menu'],
                'reduction' => $data['montant_reduction'],
                'livraison' => $data['prix_livraison'],
                'total'     => $data['prix_total'],
                'materiel'  => (int) $data['pret_materiel'],
                'id'        => $commande['commande_id'],
            ]
        );
        Stats::synchroniser((int) $commande['commande_id']);
    }

    public static function annulerParClient(array $commande): void
    {
        if ($commande['statut'] !== 'en_attente') {
            throw new \DomainException('Cette commande a déjà été acceptée : contactez-nous pour l\'annuler.');
        }
        self::annuler((int) $commande['commande_id'], 'Annulée par le client depuis son espace.', null, 'client', null);
    }

    /** RG12 : l'employé doit avoir contacté le client (téléphone ou mail) et donner un motif. */
    public static function annulerParEmploye(array $commande, string $motif, string $modeContact, int $employeId): void
    {
        if (in_array($commande['statut'], ['terminee', 'annulee'], true)) {
            throw new \DomainException('Cette commande ne peut plus être annulée.');
        }
        self::annuler((int) $commande['commande_id'], $motif, $modeContact, 'employe', $employeId);
        Mailer::send($commande['email'], 'Annulation de votre commande ' . $commande['numero_commande'], 'commande-annulation', [
            'c' => $commande, 'motif' => $motif,
        ]);
    }

    private static function annuler(int $id, string $motif, ?string $mode, string $par, ?int $employeId): void
    {
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $menuId = Database::run('SELECT menu_id FROM commande WHERE commande_id = :id FOR UPDATE', ['id' => $id])->fetchColumn();
            Database::run(
                'UPDATE commande SET statut = \'annulee\', motif_annulation = :motif, mode_contact = :mode, annulee_par = :par
                 WHERE commande_id = :id',
                ['motif' => $motif, 'mode' => $mode, 'par' => $par, 'id' => $id]
            );
            // La place libérée est remise en stock
            Database::run('UPDATE menu SET quantite_restante = quantite_restante + 1 WHERE menu_id = :m', ['m' => $menuId]);
            Database::run(
                'INSERT INTO suivi_commande (commande_id, statut, modifie_par) VALUES (:id, \'annulee\', :emp)',
                ['id' => $id, 'emp' => $employeId]
            );
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
        Stats::synchroniser($id);
    }

    public static function changerStatut(array $commande, string $nouveau, int $employeId): void
    {
        $autorises = self::TRANSITIONS[$commande['statut']] ?? [];
        if (!in_array($nouveau, $autorises, true)) {
            throw new \DomainException('Ce changement de statut n\'est pas autorisé.');
        }
        // Après livraison : « retour de matériel » seulement si du matériel a été prêté
        if ($commande['statut'] === 'livre') {
            $nouveau = $commande['pret_materiel'] ? 'en_attente_retour_materiel' : 'terminee';
        }

        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            Database::run('UPDATE commande SET statut = :s WHERE commande_id = :id', ['s' => $nouveau, 'id' => $commande['commande_id']]);
            // Passage de « retour de matériel » à « terminée » : le matériel a été restitué
            if ($commande['statut'] === 'en_attente_retour_materiel' && $nouveau === 'terminee') {
                Database::run('UPDATE commande SET restitution_materiel = 1 WHERE commande_id = :id', ['id' => $commande['commande_id']]);
            }
            Database::run(
                'INSERT INTO suivi_commande (commande_id, statut, modifie_par) VALUES (:id, :s, :emp)',
                ['id' => $commande['commande_id'], 's' => $nouveau, 'emp' => $employeId]
            );
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
        Stats::synchroniser((int) $commande['commande_id']);

        // RG14 et RG15 : notifications automatiques
        if ($nouveau === 'en_attente_retour_materiel') {
            Mailer::send($commande['email'], 'Restitution du matériel prêté - ' . $commande['numero_commande'], 'retour-materiel', ['c' => $commande]);
        } elseif ($nouveau === 'terminee') {
            Mailer::send($commande['email'], 'Votre avis nous intéresse - ' . $commande['numero_commande'], 'demande-avis', ['c' => $commande]);
        }
    }

    public static function libelle(string $statut): string
    {
        return self::STATUTS[$statut] ?? $statut;
    }
}
