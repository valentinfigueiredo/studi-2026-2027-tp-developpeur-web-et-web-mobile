<?php require __DIR__ . '/_entete.php'; ?>
<p>Bonjour <?= e($c['prenom']) ?>,</p>
<p>Votre commande <strong><?= e($c['numero_commande']) ?></strong> (<?= e($c['menu_titre']) ?>) est terminée. Nous espérons qu'elle vous a régalé !</p>
<p>Connectez-vous à votre compte pour nous laisser une note et un commentaire depuis le détail de la commande : votre avis aide d'autres clients et nous permet de progresser.</p>
<?php require __DIR__ . '/_pied.php'; ?>
