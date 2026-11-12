<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            <h1 class="mb-3">Choisir un nouveau mot de passe</h1>
            <form method="post" action="/reinitialiser" class="card card-body" novalidate>
                <?= App\Csrf::field() ?>
                <input type="hidden" name="token" value="<?= e($token) ?>">
                <?= render_partial('partials/champs-mot-de-passe', ['errors' => $errors]) ?>
                <button type="submit" class="btn btn-bordeaux">Enregistrer</button>
            </form>
        </div>
    </div>
</div>
<script src="/assets/js/password.js" defer></script>
