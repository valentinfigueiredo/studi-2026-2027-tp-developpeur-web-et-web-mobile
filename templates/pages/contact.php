<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <h1 class="mb-3">Nous contacter</h1>
            <?php if ($envoye): ?>
                <div class="alert alert-success" role="status">Merci, votre message a bien été envoyé. Nous vous répondrons rapidement par email.</div>
                <a href="/" class="btn btn-bordeaux">Retour à l'accueil</a>
            <?php else: ?>
                <p>Une question, une demande de devis pour un événement ? Écrivez-nous, nous vous répondons par email.</p>
                <form method="post" action="/contact" class="card card-body" novalidate>
                    <?= App\Csrf::field() ?>
                    <div class="mb-3">
                        <label for="titre" class="form-label">Titre de votre demande <span aria-hidden="true">*</span></label>
                        <input type="text" class="form-control<?= aria_invalid($errors, 'titre') ?>" id="titre" name="titre" value="<?= old($data, 'titre') ?>" required maxlength="150">
                        <?= field_error($errors, 'titre') ?>
                    </div>
                    <div class="mb-3">
                        <label for="message" class="form-label">Description <span aria-hidden="true">*</span></label>
                        <textarea class="form-control<?= aria_invalid($errors, 'message') ?>" id="message" name="message" rows="6" required maxlength="3000"><?= old($data, 'message') ?></textarea>
                        <?= field_error($errors, 'message') ?>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Votre adresse email (pour vous répondre) <span aria-hidden="true">*</span></label>
                        <input type="email" class="form-control<?= aria_invalid($errors, 'email') ?>" id="email" name="email" value="<?= old($data, 'email') ?>" required autocomplete="email">
                        <?= field_error($errors, 'email') ?>
                    </div>
                    <div class="pot-de-miel" aria-hidden="true">
                        <label for="site_web">Ne pas remplir ce champ</label>
                        <input type="text" id="site_web" name="site_web" tabindex="-1" autocomplete="off">
                    </div>
                    <p class="small">Votre email sert uniquement à vous répondre ; il n'est pas conservé dans notre base de données.</p>
                    <button type="submit" class="btn btn-bordeaux">Envoyer</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
