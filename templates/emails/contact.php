<?php require __DIR__ . '/_entete.php'; ?>
<p>Nouvelle demande reçue depuis le formulaire de contact du site.</p>
<p><strong>De :</strong> <?= e($email) ?><br><strong>Titre :</strong> <?= e($titre) ?></p>
<p><strong>Message :</strong><br><?= nl2br(e($message)) ?></p>
<p>Répondez directement à cet email pour répondre au visiteur.</p>
<?php require __DIR__ . '/_pied.php'; ?>
