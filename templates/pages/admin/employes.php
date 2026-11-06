<div class="container py-4">
    <h1 class="mb-3">Espace de gestion</h1>
    <?= render_partial('partials/nav-staff') ?>
    <div class="row g-4">
        <div class="col-lg-7">
            <h2 class="h4">Comptes employés</h2>
            <div class="table-responsive">
                <table class="table align-middle">
                    <caption class="visually-hidden">Liste des employés</caption>
                    <thead><tr><th scope="col">Nom</th><th scope="col">Email</th><th scope="col">État</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
                    <tbody>
                    <?php foreach ($employes as $u): ?>
                        <tr>
                            <th scope="row"><?= e($u['prenom'] . ' ' . $u['nom']) ?></th>
                            <td><?= e($u['email']) ?></td>
                            <td><?= $u['actif'] ? 'Actif' : 'Désactivé' ?></td>
                            <td>
                                <form method="post" action="/admin/employes" class="<?= $u['actif'] ? 'js-confirmer' : '' ?>" data-confirmation="Désactiver le compte de <?= e($u['prenom']) ?> ? Il ne pourra plus se connecter.">
                                    <?= App\Csrf::field() ?>
                                    <input type="hidden" name="utilisateur_id" value="<?= (int) $u['utilisateur_id'] ?>">
                                    <?php if ($u['actif']): ?>
                                        <button type="submit" name="action" value="desactiver" class="btn btn-sm btn-outline-danger">Désactiver<span class="visually-hidden"> <?= e($u['prenom'] . ' ' . $u['nom']) ?></span></button>
                                    <?php else: ?>
                                        <button type="submit" name="action" value="reactiver" class="btn btn-sm btn-outline-success">Réactiver<span class="visually-hidden"> <?= e($u['prenom'] . ' ' . $u['nom']) ?></span></button>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-lg-5">
            <form method="post" action="/admin/employes" class="card card-body" novalidate>
                <?= App\Csrf::field() ?>
                <input type="hidden" name="action" value="creer">
                <h2 class="h5">Créer un compte employé</h2>
                <p class="small">L'employé recevra un email l'informant de la création de son compte, sans son mot de passe.</p>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label for="prenom" class="form-label">Prénom <span aria-hidden="true">*</span></label>
                        <input type="text" class="form-control<?= aria_invalid($errors, 'prenom') ?>" id="prenom" name="prenom" value="<?= old($data, 'prenom') ?>" required>
                        <?= field_error($errors, 'prenom') ?>
                    </div>
                    <div class="col-6">
                        <label for="nom" class="form-label">Nom <span aria-hidden="true">*</span></label>
                        <input type="text" class="form-control<?= aria_invalid($errors, 'nom') ?>" id="nom" name="nom" value="<?= old($data, 'nom') ?>" required>
                        <?= field_error($errors, 'nom') ?>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email (identifiant) <span aria-hidden="true">*</span></label>
                    <input type="email" class="form-control<?= aria_invalid($errors, 'email') ?>" id="email" name="email" value="<?= old($data, 'email') ?>" required>
                    <?= field_error($errors, 'email') ?>
                </div>
                <?= render_partial('partials/champs-mot-de-passe', ['errors' => $errors]) ?>
                <button type="submit" class="btn btn-bordeaux">Créer le compte</button>
            </form>
        </div>
    </div>
</div>
<script src="/assets/js/confirmation.js" defer></script>
<script src="/assets/js/password.js" defer></script>
