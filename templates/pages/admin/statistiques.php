<div class="container py-4">
    <h1 class="mb-3">Espace de gestion</h1>
    <?= render_partial('partials/nav-staff') ?>
    <h2 class="h4">Statistiques des commandes</h2>
    <p class="small text-muted">Données issues de la base MongoDB. Les commandes annulées ne sont pas comptées.</p>

    <form id="filtres-stats" class="row g-2 align-items-end mb-4" aria-label="Filtrer les statistiques">
        <div class="col-sm-4">
            <label for="menu" class="form-label">Menu</label>
            <select id="menu" name="menu" class="form-select">
                <option value="">Tous les menus</option>
                <?php foreach ($menus as $m): ?>
                    <option value="<?= (int) $m['menu_id'] ?>"><?= e($m['titre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-sm-3">
            <label for="debut" class="form-label">Du</label>
            <input type="date" id="debut" name="debut" class="form-control">
        </div>
        <div class="col-sm-3">
            <label for="fin" class="form-label">Au</label>
            <input type="date" id="fin" name="fin" class="form-control">
        </div>
        <div class="col-sm-2">
            <button type="submit" class="btn btn-bordeaux w-100">Appliquer</button>
        </div>
    </form>

    <div class="row g-3 mb-4" aria-live="polite">
        <div class="col-sm-6"><div class="card card-body text-center"><p class="mb-0">Commandes</p><p class="atout-chiffre mb-0" id="kpi-commandes">-</p></div></div>
        <div class="col-sm-6"><div class="card card-body text-center"><p class="mb-0">Chiffre d'affaires TTC</p><p class="atout-chiffre mb-0" id="kpi-ca">-</p></div></div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <figure class="card card-body">
                <figcaption class="h5">Nombre de commandes par menu</figcaption>
                <canvas id="graphique" height="260" role="img" aria-label="Graphique en barres : nombre de commandes par menu. Les mêmes données sont présentées dans le tableau."></canvas>
            </figure>
        </div>
        <div class="col-lg-5">
            <!-- Tableau = alternative accessible au graphique (RGAA 1.8) -->
            <table class="table">
                <caption>Commandes et chiffre d'affaires par menu</caption>
                <thead><tr><th scope="col">Menu</th><th scope="col" class="text-end">Commandes</th><th scope="col" class="text-end">CA</th></tr></thead>
                <tbody id="tableau-stats"><tr><td colspan="3">Chargement...</td></tr></tbody>
            </table>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js" integrity="sha384-NrKB+u6Ts6AtkIhwPixiKTzgSKNblyhlk0Sohlgar9UHUBzai/sgnNNWWd291xqt" crossorigin="anonymous" defer></script>
