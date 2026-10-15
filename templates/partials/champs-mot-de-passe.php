<?php /** @var array $errors */ ?>
<div class="mb-3">
    <label for="password" class="form-label">Mot de passe <span aria-hidden="true">*</span></label>
    <input type="password" class="form-control<?= aria_invalid($errors, 'password') ?>" id="password" name="password"
           required minlength="10" autocomplete="new-password" aria-describedby="regles-mdp">
    <?= field_error($errors, 'password') ?>
    <!-- Règles affichées en permanence, cochées en direct par password.js -->
    <ul id="regles-mdp" class="regles-mdp small mt-2 mb-0">
        <li data-regle="longueur">10 caractères minimum</li>
        <li data-regle="majuscule">Une lettre majuscule</li>
        <li data-regle="minuscule">Une lettre minuscule</li>
        <li data-regle="chiffre">Un chiffre</li>
        <li data-regle="special">Un caractère spécial (! @ # ? ...)</li>
    </ul>
</div>
<div class="mb-3">
    <label for="password_confirmation" class="form-label">Confirmez le mot de passe <span aria-hidden="true">*</span></label>
    <input type="password" class="form-control<?= aria_invalid($errors, 'password_confirmation') ?>" id="password_confirmation"
           name="password_confirmation" required autocomplete="new-password">
    <?= field_error($errors, 'password_confirmation') ?>
</div>
