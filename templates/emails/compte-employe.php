<?php require __DIR__ . '/_entete.php'; ?>
<p>Bonjour <?= e($prenom) ?>,</p>
<p>Un compte <strong>employé</strong> a été créé pour vous sur l'application Vite &amp; Gourmand.</p>
<p>Votre identifiant de connexion est votre adresse email : <strong><?= e($email) ?></strong>.</p>
<p>Pour des raisons de sécurité, votre mot de passe ne figure pas dans cet email : rapprochez-vous de l'administrateur pour l'obtenir.</p>
<?php require __DIR__ . '/_pied.php'; ?>
