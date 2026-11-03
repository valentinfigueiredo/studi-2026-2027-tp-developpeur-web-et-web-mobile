<?php $libelles = ['en_attente' => 'En attente', 'valide' => 'Publié', 'refuse' => 'Refusé']; ?>
<div class="container py-4">
    <h1 class="mb-3">Espace de gestion</h1>
    <?= render_partial('partials/nav-staff') ?>
    <h2 class="h4">Avis clients</h2>
    <?php if (!$avis): ?><p>Aucun avis pour le moment.</p><?php endif; ?>
    <div class="row g-3">
        <?php foreach ($avis as $a): ?>
            <div class="col-md-6">
                <article class="card h-100<?= $a['statut'] === 'en_attente' ? ' border-or' : '' ?>">
                    <div class="card-body">
                        <p class="mb-1"><span class="badge avis-<?= e($a['statut']) ?>"><?= e($libelles[$a['statut']]) ?></span></p>
                        <h3 class="h6 mb-1"><?= e($a['prenom'] . ' ' . $a['nom']) ?> - <?= e($a['menu']) ?> (<?= e($a['numero_commande']) ?>)</h3>
                        <p class="etoiles mb-1" aria-label="Note : <?= (int) $a['note'] ?> sur 5"><span aria-hidden="true"><?= etoiles((int) $a['note']) ?></span></p>
                        <p><?= e($a['commentaire']) ?></p>
                        <p class="small text-muted"><?= e(date_fr($a['date_avis'], true)) ?></p>
                        <form method="post" action="/employe/avis" class="d-flex gap-2">
                            <?= App\Csrf::field() ?>
                            <input type="hidden" name="avis_id" value="<?= (int) $a['avis_id'] ?>">
                            <?php if ($a['statut'] !== 'valide'): ?>
                                <button type="submit" name="decision" value="valide" class="btn btn-sm btn-success">Valider</button>
                            <?php endif; ?>
                            <?php if ($a['statut'] !== 'refuse'): ?>
                                <button type="submit" name="decision" value="refuse" class="btn btn-sm btn-outline-danger">Refuser</button>
                            <?php endif; ?>
                        </form>
                    </div>
                </article>
            </div>
        <?php endforeach; ?>
    </div>
</div>
