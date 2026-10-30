<?php require __DIR__ . '/_entete.php'; ?>
<p>Bonjour <?= e($c['prenom']) ?>,</p>
<p>Votre commande <strong><?= e($c['numero_commande']) ?></strong> a été livrée avec du matériel prêté.</p>
<p><strong>Merci de nous restituer ce matériel sous 10 jours ouvrés.</strong> Passé ce délai, des frais de
<strong>600 €</strong> vous seront facturés, conformément à nos conditions générales de vente.</p>
<p>Pour organiser la restitution, contactez-nous par téléphone au 05 56 00 00 00 ou via la page Contact du site.</p>
<?php require __DIR__ . '/_pied.php'; ?>
