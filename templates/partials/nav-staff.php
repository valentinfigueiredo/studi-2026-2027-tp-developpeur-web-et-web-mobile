<?php use App\Auth; ?>
<nav aria-label="Menu de l'espace de gestion" class="mb-4">
    <ul class="nav nav-pills nav-staff flex-wrap">
        <li class="nav-item"><a class="nav-link<?= nav_active('/employe', true) ?>" href="/employe">Commandes</a></li>
        <li class="nav-item"><a class="nav-link<?= nav_active('/employe/menus') ?>" href="/employe/menus">Menus</a></li>
        <li class="nav-item"><a class="nav-link<?= nav_active('/employe/plats') ?>" href="/employe/plats">Plats</a></li>
        <li class="nav-item"><a class="nav-link<?= nav_active('/employe/horaires') ?>" href="/employe/horaires">Horaires</a></li>
        <li class="nav-item"><a class="nav-link<?= nav_active('/employe/avis') ?>" href="/employe/avis">Avis</a></li>
        <?php if (Auth::hasRole(Auth::ADMIN)): ?>
            <li class="nav-item"><a class="nav-link<?= nav_active('/admin/employes') ?>" href="/admin/employes">Employés</a></li>
            <li class="nav-item"><a class="nav-link<?= nav_active('/admin/statistiques') ?>" href="/admin/statistiques">Statistiques</a></li>
        <?php endif; ?>
    </ul>
</nav>
