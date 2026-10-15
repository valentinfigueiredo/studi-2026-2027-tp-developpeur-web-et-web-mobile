<?php require __DIR__ . '/_entete.php'; ?>
<p>Bonjour <?= e($prenom) ?>,</p>
<p>Vous avez demandé à réinitialiser votre mot de passe. Cliquez sur le lien ci-dessous (valable 1 heure) :</p>
<p><a href="<?= e($lien) ?>" style="color:#7A1F3D;font-weight:bold;">Choisir un nouveau mot de passe</a></p>
<p>Si vous n'êtes pas à l'origine de cette demande, ignorez cet email : votre mot de passe reste inchangé.</p>
<?php require __DIR__ . '/_pied.php'; ?>
