<div class="container py-4">
    <h1 class="mb-3">Espace de gestion</h1>
    <?= render_partial('partials/nav-staff') ?>
    <h2 class="h4">Horaires d'ouverture</h2>
    <p>Ces horaires sont affichés dans le pied de page du site.</p>
    <?php if ($errors): ?>
        <div class="alert alert-danger" role="alert"><ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <form method="post" action="/employe/horaires" class="card card-body col-lg-8" novalidate>
        <?= App\Csrf::field() ?>
        <table class="table align-middle mb-3">
            <caption class="visually-hidden">Horaires par jour de la semaine</caption>
            <thead><tr><th scope="col">Jour</th><th scope="col">Ouverture</th><th scope="col">Fermeture</th><th scope="col">Fermé</th></tr></thead>
            <tbody>
            <?php foreach ($horaires as $h): $id = (int) $h['horaire_id']; $ferme = $_POST ? !empty($_POST['horaires'][$id]['ferme']) : (bool) $h['ferme']; ?>
                <tr>
                    <th scope="row"><?= e(ucfirst($h['jour'])) ?></th>
                    <td><label class="visually-hidden" for="o-<?= $id ?>">Ouverture le <?= e($h['jour']) ?></label>
                        <input type="time" class="form-control" id="o-<?= $id ?>" name="horaires[<?= $id ?>][ouverture]" value="<?= e($_POST['horaires'][$id]['ouverture'] ?? substr((string) $h['heure_ouverture'], 0, 5)) ?>"></td>
                    <td><label class="visually-hidden" for="f-<?= $id ?>">Fermeture le <?= e($h['jour']) ?></label>
                        <input type="time" class="form-control" id="f-<?= $id ?>" name="horaires[<?= $id ?>][fermeture]" value="<?= e($_POST['horaires'][$id]['fermeture'] ?? substr((string) $h['heure_fermeture'], 0, 5)) ?>"></td>
                    <td><div class="form-check">
                        <input class="form-check-input" type="checkbox" id="x-<?= $id ?>" name="horaires[<?= $id ?>][ferme]" value="1" <?= $ferme ? 'checked' : '' ?>>
                        <label class="form-check-label" for="x-<?= $id ?>"><span class="visually-hidden">Fermé le <?= e($h['jour']) ?></span><span aria-hidden="true">Fermé</span></label>
                    </div></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <button type="submit" class="btn btn-bordeaux align-self-start">Enregistrer les horaires</button>
    </form>
</div>
