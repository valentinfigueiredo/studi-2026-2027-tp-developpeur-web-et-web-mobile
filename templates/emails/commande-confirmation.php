<?php require __DIR__ . '/_entete.php'; ?>
<p>Bonjour <?= e($c['prenom']) ?>,</p>
<p>Nous avons bien reçu votre commande <strong><?= e($c['numero_commande']) ?></strong>. Elle sera validée très prochainement par notre équipe.</p>
<table role="presentation" cellpadding="6" style="border-collapse:collapse;">
<tr><td>Menu</td><td><strong><?= e($c['menu_titre']) ?></strong></td></tr>
<tr><td>Prestation</td><td><?= e(date_fr($c['date_prestation'])) ?> à <?= e(heure_fr($c['heure_livraison'])) ?></td></tr>
<tr><td>Adresse</td><td><?= e($c['adresse_prestation'] . ', ' . $c['code_postal'] . ' ' . $c['ville_prestation']) ?></td></tr>
<tr><td>Personnes</td><td><?= (int) $c['nombre_personne'] ?></td></tr>
<tr><td>Prix du menu</td><td><?= money($c['prix_menu']) ?></td></tr>
<?php if ((float) $c['montant_reduction'] > 0): ?><tr><td>Réduction (10 %)</td><td>- <?= money($c['montant_reduction']) ?></td></tr><?php endif; ?>
<tr><td>Livraison</td><td><?= money($c['prix_livraison']) ?></td></tr>
<tr><td><strong>Total</strong></td><td><strong><?= money($c['prix_total']) ?></strong></td></tr>
</table>
<p><strong>Rappel des conditions du menu :</strong><br><?= nl2br(e($c['menu_conditions'])) ?></p>
<p>Vous pouvez suivre, modifier ou annuler votre commande depuis votre espace client tant qu'elle n'a pas été acceptée.</p>
<?php require __DIR__ . '/_pied.php'; ?>
