<?php require __DIR__ . '/_entete.php'; ?>
<p>Bonjour <?= e($c['prenom']) ?>,</p>
<p>Comme convenu lors de notre échange, votre commande <strong><?= e($c['numero_commande']) ?></strong> a été annulée.</p>
<p><strong>Motif :</strong> <?= e($motif) ?></p>
<p>Nous restons à votre disposition pour toute nouvelle commande.</p>
<?php require __DIR__ . '/_pied.php'; ?>
