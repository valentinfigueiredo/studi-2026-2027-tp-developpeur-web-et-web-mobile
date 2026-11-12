<?php
/** Champs communs aux formulaires de commande et de modification de commande. */
/** @var array $data  @var array $errors */
$demain = (new DateTimeImmutable('tomorrow'))->format('Y-m-d');
?>
<fieldset class="mb-4">
    <legend class="h5">2. La prestation</legend>
    <div class="row g-3">
        <div class="col-sm-6">
            <label for="date_prestation" class="form-label">Date de la prestation <span aria-hidden="true">*</span></label>
            <input type="date" class="form-control<?= aria_invalid($errors, 'date_prestation') ?>" id="date_prestation" name="date_prestation"
                   min="<?= e($demain) ?>" value="<?= old($data, 'date_prestation') ?>" required>
            <?= field_error($errors, 'date_prestation') ?>
        </div>
        <div class="col-sm-6">
            <label for="heure_livraison" class="form-label">Heure de livraison souhaitée <span aria-hidden="true">*</span></label>
            <input type="time" class="form-control<?= aria_invalid($errors, 'heure_livraison') ?>" id="heure_livraison" name="heure_livraison"
                   value="<?= old($data, 'heure_livraison') ?>" required>
            <?= field_error($errors, 'heure_livraison') ?>
        </div>
        <div class="col-12 position-relative">
            <label for="adresse_prestation" class="form-label">Adresse de livraison <span aria-hidden="true">*</span></label>
            <input type="text" class="form-control<?= aria_invalid($errors, 'adresse_prestation') ?>" id="adresse_prestation" name="adresse_prestation"
                   value="<?= old($data, 'adresse_prestation') ?>" required autocomplete="off"
                   role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="suggestions-adresse">
            <ul id="suggestions-adresse" class="list-group suggestions d-none" role="listbox" aria-label="Suggestions d'adresses"></ul>
            <?= field_error($errors, 'adresse_prestation') ?>
            <div class="form-text">Commencez à taper puis choisissez votre adresse dans la liste pour calculer les frais de livraison.</div>
        </div>
        <div class="col-sm-4">
            <label for="code_postal" class="form-label">Code postal <span aria-hidden="true">*</span></label>
            <input type="text" inputmode="numeric" class="form-control<?= aria_invalid($errors, 'code_postal') ?>" id="code_postal" name="code_postal"
                   value="<?= old($data, 'code_postal') ?>" required>
            <?= field_error($errors, 'code_postal') ?>
        </div>
        <div class="col-sm-8">
            <label for="ville_prestation" class="form-label">Ville <span aria-hidden="true">*</span></label>
            <input type="text" class="form-control<?= aria_invalid($errors, 'ville_prestation') ?>" id="ville_prestation" name="ville_prestation"
                   value="<?= old($data, 'ville_prestation') ?>" required>
            <?= field_error($errors, 'ville_prestation') ?>
        </div>
        <input type="hidden" name="latitude" id="latitude" value="<?= old($_POST, 'latitude') ?>">
        <input type="hidden" name="longitude" id="longitude" value="<?= old($_POST, 'longitude') ?>">
    </div>
</fieldset>

<fieldset class="mb-4">
    <legend class="h5">3. Les convives</legend>
    <div class="row g-3">
        <div class="col-sm-6">
            <label for="nombre_personne" class="form-label">Nombre de personnes <span aria-hidden="true">*</span></label>
            <input type="number" class="form-control<?= aria_invalid($errors, 'nombre_personne') ?>" id="nombre_personne" name="nombre_personne"
                   value="<?= old($data, 'nombre_personne') ?>" required min="1" aria-describedby="aide-personnes">
            <div id="aide-personnes" class="form-text">Minimum imposé par le menu. -10 % à partir du minimum + 5 personnes.</div>
            <?= field_error($errors, 'nombre_personne') ?>
        </div>
        <div class="col-sm-6 d-flex align-items-end">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="pret_materiel" name="pret_materiel" value="1" <?= !empty($data['pret_materiel']) ? 'checked' : '' ?>>
                <label class="form-check-label" for="pret_materiel">J'ai besoin d'un prêt de matériel (vaisselle, réchauds)</label>
            </div>
        </div>
    </div>
</fieldset>

<section class="card recap-prix mb-4" aria-labelledby="titre-recap">
    <div class="card-body">
        <h2 id="titre-recap" class="h5">Détail du prix</h2>
        <!-- Mis à jour en direct par commande.js ; le prix définitif est recalculé par le serveur -->
        <dl class="row mb-0" id="recap" aria-live="polite">
            <dt class="col-7">Prix du menu</dt><dd class="col-5 text-end" id="recap-menu">-</dd>
            <dt class="col-7">Réduction (10 %)</dt><dd class="col-5 text-end" id="recap-reduction">-</dd>
            <dt class="col-7">Livraison <span class="small fw-normal" id="recap-distance"></span></dt><dd class="col-5 text-end" id="recap-livraison">-</dd>
            <dt class="col-7 total">Total</dt><dd class="col-5 text-end total" id="recap-total">-</dd>
        </dl>
        <p class="small text-muted mb-0 mt-2">Livraison offerte à Bordeaux. Ailleurs : 5 € + 0,59 € par kilomètre depuis Bordeaux.</p>
    </div>
</section>
