<?php
    require_once __DIR__ . '/../config.php';
    require_once PROTECTION_CLASS_PROJET;
    require_once CHAMBRE_FROIDES_CLASS_PROJET;

    session_start();
    $protection = new Protection();
    $protection->url_protection();
    $protection->tfa_url_protection();

    $titre_nav = 'Supervision';
    require_once NAVBAR;
    
    $chambres = new Chambre_froides();

    // Liste de toutes les chambres (avec type + plages)
    $liste_chambres = $chambres->info_chambres();

    // Chambre sélectionnée
    $id_chambre = isset($_GET['id_chambre']) ? (int)$_GET['id_chambre'] : null;

    if ($id_chambre === null && !empty($liste_chambres)) 
    {
        $id_chambre = (int)$liste_chambres[0]['id_chambre'];
    }

    // ====================== INFOS CHAMBRE SÉLECTIONNÉE ======================
    $nom_chambre   = 'Chambre inconnue';
    $type_chambre  = 0;
    $plage_min     = -10;
    $plage_max     = 20;

    foreach ($liste_chambres as $ch)
    {
        if ((int)$ch['id_chambre'] === $id_chambre)
        {
            $nom_chambre  = $ch['nom_chambre'];
            $type_chambre = (int)$ch['type_chambre'];
            $plage_min    = (float)$ch['plage_min'];
            $plage_max    = (float)$ch['plage_max'];

            if ($plage_min > $plage_max) 
            {
                [$plage_min, $plage_max] = [$plage_max, $plage_min];
            }

            break;
        }
    }

    $type_label = $type_chambre === 1 ? 'Positive' : 'Négative';
    $badge_color = $type_chambre === 1 ? 'success' : 'info';

    $definition_type = $type_chambre === 1 
        ? 'Chambre froide positive : température supérieure à 0°C (généralement +3°C)'
        : 'Chambre froide négative : température en dessous de 0°C (généralement -20°C à -60°C)';

    // ====================== PÉRIODE ======================
    $periode = $_GET['periode'] ?? 'jour';
    $now = time();
    $start = $now - 86400;
    $format_label = 'H:i';
    $end = $now;

    $date_debut = $_GET['date_debut'] ?? null;
    $date_fin   = $_GET['date_fin']   ?? null;

    if (!$date_debut || !$date_fin)
    {
        switch ($periode) 
        {
            case 'semaine': $start = $now - (7 * 86400);   $format_label = 'd/m H:i'; break;
            case 'mois':    $start = $now - (30 * 86400);  $format_label = 'd/m H:i'; break;
            case 'annee':   $start = $now - (365 * 86400); $format_label = 'd/m/Y H:i';   break;
        }
    }

    if ($date_debut && $date_fin) 
    {
        $ts_debut = strtotime(str_replace('T', ' ', $date_debut));
        $ts_fin   = strtotime(str_replace('T', ' ', $date_fin));

        if ($ts_debut > $ts_fin) 
        {
            [$ts_debut, $ts_fin] = [$ts_fin, $ts_debut];
        }

        $start = $ts_debut;
        $end   = $ts_fin;
        $duree = $end - $start;

        if ($duree <= 2 * 86400)      $format_label = 'd/m H:i';
        elseif ($duree <= 15 * 86400) $format_label = 'd/m/Y H:i';
        else                          $format_label = 'd/m/Y H:i';
    }

    // ====================== PAGINATION ======================
    $par_page    = 50;
    $page_temp   = max(1, (int)($_GET['page_temp']   ?? 1));
    $page_porte  = max(1, (int)($_GET['page_porte']  ?? 1));
    $page_alerte = max(1, (int)($_GET['page_alerte'] ?? 1));

    // ====================== BDD + DONNÉES ======================
    $labels       = [];
    $temps        = [];
    $rows         = [];
    $rows_portes  = [];
    $rows_alertes = [];

    $erreur_connexion = '';
    try 
    {
        $rows         = $chambres->info_temps($id_chambre, $start, $end);
        $rows_portes  = $chambres->info_portes($id_chambre, $start, $end);
        $rows_alertes = $chambres->info_alertes($id_chambre, $start, $end);
    } 
    catch (Exception $e) 
    {
        $erreur_connexion = "Impossible de récupérer les données.";
        $rows         = [];
        $rows_portes  = [];
        $rows_alertes = [];
    }

    foreach ($rows as $row) 
    {
        $labels[] = date($format_label, (int)$row['horodatage_temperature']);
        $temps[]  = (float)$row['temperature'];
    }

    $data = ['labels' => $labels, 'temp' => $temps];

    if (empty($labels)) 
    {
        $labels = ['Aucune donnée'];
        $temps  = [0];
    }

    $rows_historique = !empty($rows) ? array_reverse($rows) : [];

    $min_temp = $max_temp = $avg_temp = null;
    $nb_mesures = count($rows);

    if ($nb_mesures > 0)
    {
        $min_temp = min($temps);
        $max_temp = max($temps);
        $avg_temp = round(array_sum($temps) / $nb_mesures, 1);
    }

    // ====================== PAGINATION CALCULS ======================
    $total_temp       = count($rows_historique);
    $total_pages_temp = max(1, (int)ceil($total_temp / $par_page));
    $page_temp        = min($page_temp, $total_pages_temp);
    $offset_temp      = ($page_temp - 1) * $par_page;
    $rows_page_temp   = array_slice($rows_historique, $offset_temp, $par_page);

    $total_portes       = count($rows_portes);
    $total_pages_porte  = max(1, (int)ceil($total_portes / $par_page));
    $page_porte         = min($page_porte, $total_pages_porte);
    $offset_porte       = ($page_porte - 1) * $par_page;
    $rows_page_porte    = array_slice($rows_portes, $offset_porte, $par_page);

    $total_alertes       = count($rows_alertes);
    $total_pages_alerte  = max(1, (int)ceil($total_alertes / $par_page));
    $page_alerte         = min($page_alerte, $total_pages_alerte);
    $offset_alerte       = ($page_alerte - 1) * $par_page;
    $rows_page_alerte    = array_slice($rows_alertes, $offset_alerte, $par_page);

    // Fonction helper pour construire les liens de pagination en conservant les GET existants
    function pagination_url(array $overrides): string
    {
        $params = array_merge($_GET, $overrides);
        return '?' . http_build_query($params);
    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mon Espace - Températures</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        #btn-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 999;
            display: none;
            border-radius: 50%;
            width: 48px;
            height: 48px;
            font-size: 1.2rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.25);
        }
    </style>
</head>
<body class="bg-light" id="top">

    <!-- Navbar -->
    <!-- require NAVBAR injecté depuis le PHP -->

    <div class="container mt-4">

        <!-- Sélecteurs -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Chambre froide :</label>
                        <select name="id_chambre" class="form-select" onchange="this.form.submit()">
                            <?php foreach ($liste_chambres as $ch): 
                                $t = (int)$ch['type_chambre'];
                                $lbl = $t === 1 ? 'Positive' : 'Négative';
                            ?>
                                <option value="<?= $ch['id_chambre'] ?>" 
                                        <?= ((int)$ch['id_chambre'] === $id_chambre) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($ch['nom_chambre']) ?> (<?= $lbl ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Plage rapide :</label>
                        <select name="periode" class="form-select" onchange="
                            document.querySelector('[name=date_debut]').value = '';
                            document.querySelector('[name=date_fin]').value = '';
                            this.form.submit();
                        ">
                            <option value="jour"   <?= $periode === 'jour'   ? 'selected' : '' ?>>24 heures</option>
                            <option value="semaine"<?= $periode === 'semaine'? 'selected' : '' ?>>1 semaine</option>
                            <option value="mois"   <?= $periode === 'mois'   ? 'selected' : '' ?>>1 mois</option>
                            <option value="annee"  <?= $periode === 'annee'  ? 'selected' : '' ?>>1 année</option>
                        </select>
                    </div>

                    <div class="col-md-5">
                        <label class="form-label fw-bold">Plage personnalisée :</label>
                        <div class="input-group">
                            <input type="datetime-local" name="date_debut" class="form-control" 
                                   value="<?= $date_debut ? date('Y-m-d\TH:i', strtotime($date_debut)) : '' ?>">
                            <span class="input-group-text">→</span>
                            <input type="datetime-local" name="date_fin" class="form-control" 
                                   value="<?= $date_fin ? date('Y-m-d\TH:i', strtotime($date_fin)) : '' ?>">
                        </div>
                        <button type="submit" class="btn btn-primary mt-2 w-100">
                            <i class="fas fa-search"></i> Appliquer la plage
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($erreur_connexion !== ''): ?>
            <div class="alert alert-danger"><?= $erreur_connexion ?></div>
        <?php else: ?>

            <!-- GRAPHIQUE -->
            <div class="card mb-4">
                <div class="card-header fw-bold">
                    <i class="fas fa-thermometer-half"></i> 
                    Évolution de la température – <?= htmlspecialchars($nom_chambre) ?>
                </div>
                <div class="card-body">
                    <?php if (empty($rows)): ?>
                        <div class="alert alert-info text-center py-4">
                            Aucune donnée de température disponible pour cette plage.
                        </div>
                    <?php else: ?>
                        <canvas id="tempChart" style="max-height: 420px;"></canvas>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ===================== TABLEAU HISTORIQUE TEMPÉRATURES ===================== -->
            <div class="card mb-4" id="tableau-temperatures">
                <div class="card-header fw-bold d-flex justify-content-between align-items-center">
                    <span>
                        <i class="fas fa-history"></i> 
                        Historique températures – <?= htmlspecialchars($nom_chambre) ?>
                    </span>
                    <span class="badge bg-light text-dark">
                        Plage : <?= $plage_min ?> °C → <?= $plage_max ?> °C
                    </span>
                </div>
                <div class="card-body">
                    <?php if (empty($rows_historique)): ?>
                        <div class="alert alert-info text-center py-4">
                            Aucune donnée disponible pour le moment.
                        </div>
                    <?php else: ?>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <small class="text-muted">
                                <?= $total_temp ?> enregistrement(s) — page <?= $page_temp ?> / <?= $total_pages_temp ?>
                            </small>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover table-striped align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th style="width: 35%;">Date et heure</th>
                                        <th class="text-center" style="width: 25%;">Statut</th>
                                        <th class="text-end">Température</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rows_page_temp as $row):
                                        $temp = round((float)$row['temperature'], 1);
                                        $is_conforme = ($temp >= $plage_min && $temp <= $plage_max);
                                        $statut_class = $is_conforme ? 'success' : 'danger';
                                        $statut_title = $is_conforme ? 'Conforme' : 'Hors plage';
                                    ?>
                                    <tr>
                                        <td><?= date('d/m/Y à H:i:s', (int)$row['horodatage_temperature']) ?></td>
                                        <td class="text-center">
                                            <i class="fas fa-circle fa-lg text-<?= $statut_class ?>" 
                                               title="<?= $statut_title ?>"></i>
                                        </td>
                                        <td class="text-end fw-semibold">
                                            <?= number_format($temp, 1) ?> °C
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td><strong>Résumé global</strong></td>
                                        <td></td>
                                        <td class="text-end">
                                            <strong>Min :</strong> <?= $min_temp ?> °C &nbsp;
                                            <strong>Max :</strong> <?= $max_temp ?> °C &nbsp;
                                            <strong>Moy :</strong> <?= $avg_temp ?> °C
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Pagination températures -->
                        <?php if ($total_pages_temp > 1): ?>
                        <nav aria-label="Pagination températures">
                            <ul class="pagination pagination-sm justify-content-center mt-3 mb-0">
                                <li class="page-item <?= $page_temp <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= pagination_url(['page_temp' => $page_temp - 1]) ?>#tableau-temperatures">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                                <?php for ($p = 1; $p <= $total_pages_temp; $p++): ?>
                                    <?php if ($p === 1 || $p === $total_pages_temp || abs($p - $page_temp) <= 2): ?>
                                    <li class="page-item <?= $p === $page_temp ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= pagination_url(['page_temp' => $p]) ?>#tableau-temperatures"><?= $p ?></a>
                                    </li>
                                    <?php elseif (abs($p - $page_temp) === 3): ?>
                                    <li class="page-item disabled"><span class="page-link">…</span></li>
                                    <?php endif; ?>
                                <?php endfor; ?>
                                <li class="page-item <?= $page_temp >= $total_pages_temp ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= pagination_url(['page_temp' => $page_temp + 1]) ?>#tableau-temperatures">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                        <?php endif; ?>

                    <?php endif; ?>
                </div>
            </div>

            <!-- ===================== TABLEAU ÉTATS PORTES ===================== -->
            <div class="card mb-4" id="tableau-portes">
                <div class="card-header fw-bold">
                    <i class="fas fa-door-open"></i> Historique états des portes – <?= htmlspecialchars($nom_chambre) ?>
                    <?php if ($total_portes > 0): ?>
                        <span class="badge bg-secondary ms-2"><?= $total_portes ?> enregistrement(s)</span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (empty($rows_portes)): ?>
                        <div class="alert alert-info text-center py-4">
                            Aucun enregistrement de porte pour <?= htmlspecialchars($nom_chambre) ?> sur cette période.
                        </div>
                    <?php else: ?>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <small class="text-muted">
                                Page <?= $page_porte ?> / <?= $total_pages_porte ?>
                            </small>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover table-striped align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th style="width: 15%;"># ID</th>
                                        <th style="width: 35%;">Date et heure</th>
                                        <th class="text-center">État</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rows_page_porte as $porte):
                                        $etat        = (int)$porte['etat_porte'];
                                        $est_ouverte = $etat === 1;
                                        $etat_label  = $est_ouverte ? 'Ouverte' : 'Fermée';
                                        $etat_icon   = $est_ouverte ? 'fa-door-open text-warning' : 'fa-door-closed text-success';
                                    ?>
                                    <tr>
                                        <td class="text-muted">#<?= (int)$porte['id_enregistrement_porte'] ?></td>
                                        <td><?= date('d/m/Y à H:i:s', (int)$porte['horodatage_porte']) ?></td>
                                        <td class="text-center">
                                            <i class="fas <?= $etat_icon ?> fa-lg me-1" title="<?= $etat_label ?>"></i>
                                            <span class="fw-semibold"><?= $etat_label ?></span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination portes -->
                        <?php if ($total_pages_porte > 1): ?>
                        <nav aria-label="Pagination portes">
                            <ul class="pagination pagination-sm justify-content-center mt-3 mb-0">
                                <li class="page-item <?= $page_porte <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= pagination_url(['page_porte' => $page_porte - 1]) ?>#tableau-portes">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                                <?php for ($p = 1; $p <= $total_pages_porte; $p++): ?>
                                    <?php if ($p === 1 || $p === $total_pages_porte || abs($p - $page_porte) <= 2): ?>
                                    <li class="page-item <?= $p === $page_porte ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= pagination_url(['page_porte' => $p]) ?>#tableau-portes"><?= $p ?></a>
                                    </li>
                                    <?php elseif (abs($p - $page_porte) === 3): ?>
                                    <li class="page-item disabled"><span class="page-link">…</span></li>
                                    <?php endif; ?>
                                <?php endfor; ?>
                                <li class="page-item <?= $page_porte >= $total_pages_porte ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= pagination_url(['page_porte' => $page_porte + 1]) ?>#tableau-portes">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                        <?php endif; ?>

                    <?php endif; ?>
                </div>
            </div>

            <!-- ===================== TABLEAU ALERTES ===================== -->
            <div class="card mb-4" id="tableau-alertes">
                <div class="card-header fw-bold">
                    <i class="fas fa-bell"></i> Historique alertes – <?= htmlspecialchars($nom_chambre) ?>
                    <?php if ($total_alertes > 0): ?>
                        <span class="badge bg-danger ms-2"><?= $total_alertes ?> alerte(s)</span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (empty($rows_alertes)): ?>
                        <div class="alert alert-success text-center py-4">
                            <i class="fas fa-check-circle me-2"></i>
                            Aucune alerte pour <?= htmlspecialchars($nom_chambre) ?> sur cette période.
                        </div>
                    <?php else: ?>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <small class="text-muted">
                                <?= $total_alertes ?> alerte(s) — page <?= $page_alerte ?> / <?= $total_pages_alerte ?>
                            </small>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover table-striped align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th style="width: 10%;"># ID</th>
                                        <th style="width: 28%;">Date et heure</th>
                                        <th>Type d'alerte</th>
                                        <th class="text-center">Acquittement</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rows_page_alerte as $alerte):
                                        $ack           = (int)$alerte['date_ack_alarme'];
                                        $est_acquittee = $ack > 0;

                                        switch ((int)$alerte['type_alerte']) 
                                        {
                                            case 0:
                                                $type_label = 'Seuil minimum dépassé';
                                                $type_icon  = 'fa-temperature-arrow-down text-primary';
                                                break;
                                            case 1:
                                                $type_label = 'Seuil maximum dépassé';
                                                $type_icon  = 'fa-temperature-arrow-up text-danger';
                                                break;
                                            case 2:
                                                $type_label = 'Porte ouverte trop longtemps';
                                                $type_icon  = 'fa-door-open text-warning';
                                                break;
                                            default:
                                                $type_label = 'Type inconnu';
                                                $type_icon  = 'fa-question-circle text-secondary';
                                            }
                                    ?>
                                    <tr class="<?= $est_acquittee ? '' : 'table-danger' ?>">
                                        <td class="text-muted">#<?= (int)$alerte['id_alerte'] ?></td>
                                        <td><?= date('d/m/Y à H:i:s', (int)$alerte['horodatage_alerte']) ?></td>
                                        <td>
                                            <i class="fas <?= $type_icon ?> me-1"></i>
                                            <?= $type_label ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($est_acquittee): ?>
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check me-1"></i>
                                                    <?= date('d/m/Y H:i', $ack) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-clock me-1"></i> Non acquittée
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                                

                        <!-- Pagination alertes -->
                        <?php if ($total_pages_alerte > 1): ?>
                        <nav aria-label="Pagination alertes">
                            <ul class="pagination pagination-sm justify-content-center mt-3 mb-0">
                                <li class="page-item <?= $page_alerte <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= pagination_url(['page_alerte' => $page_alerte - 1]) ?>#tableau-alertes">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                                <?php for ($p = 1; $p <= $total_pages_alerte; $p++): ?>
                                    <?php if ($p === 1 || $p === $total_pages_alerte || abs($p - $page_alerte) <= 2): ?>
                                    <li class="page-item <?= $p === $page_alerte ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= pagination_url(['page_alerte' => $p]) ?>#tableau-alertes"><?= $p ?></a>
                                    </li>
                                    <?php elseif (abs($p - $page_alerte) === 3): ?>
                                    <li class="page-item disabled"><span class="page-link">…</span></li>
                                    <?php endif; ?>
                                <?php endfor; ?>
                                <li class="page-item <?= $page_alerte >= $total_pages_alerte ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= pagination_url(['page_alerte' => $page_alerte + 1]) ?>#tableau-alertes">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                        <?php endif; ?>

                    <?php endif; ?>
                </div>
                <div class="card-footer text-end">
                    <a href="../espaces_perso/espace_personnel.php" class="btn btn-secondary">Retour</a>
                </div>
            </div>

        <?php endif; ?>
    </div>

    <!-- ===================== BOUTON RETOUR EN HAUT ===================== -->
    <button id="btn-top" class="btn btn-dark" onclick="window.scrollTo({top:0, behavior:'smooth'})" title="Retour en haut">
        <i class="fas fa-arrow-up"></i>
    </button>

    <?php if (!empty($rows)): ?>
    <?php
        // Si un seul point : dupliquer pour que Chart.js puisse tracer une ligne
        if (count($data['labels']) === 1)
        {
            $data['labels'][] = $data['labels'][0] . ' ';
            $data['temp'][]   = $data['temp'][0];
        }
        $seuil_min = array_fill(0, count($data['labels']), $plage_min);
        $seuil_max = array_fill(0, count($data['labels']), $plage_max);
    ?>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        new Chart(document.getElementById('tempChart'), {
            type: 'line',
            data: {
                labels: <?= json_encode($data['labels']) ?>,
                datasets: [
                    {
                        label: <?= json_encode($nom_chambre . ' (°C)') ?>,
                        data: <?= json_encode($data['temp']) ?>,
                        borderColor: '#fd0d0dff',
                        backgroundColor: 'rgba(253, 13, 13, 0.1)',
                        tension: 0.3,
                        borderWidth: 2,
                        pointRadius: 2
                    },
                    {
                        label: 'Seuil minimum',
                        data: <?= json_encode($seuil_min) ?>,
                        borderColor: 'blue',
                        borderDash: [5, 5],
                        pointRadius: 0,
                        borderWidth: 2,
                        tooltip: { enabled: false }
                    },
                    {
                        label: 'Seuil maximum',
                        data: <?= json_encode($seuil_max) ?>,
                        borderColor: 'green',
                        borderDash: [5, 5],
                        pointRadius: 0,
                        borderWidth: 2,
                        tooltip: { enabled: false }
                    }
                ]
            },
            options: {
                responsive: true,
                spanGaps: true,
                scales: {
                    y: { title: { display: true, text: 'Température (°C)' }}
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            pointStyle: 'line',
                        }
                    }
                }
            }
        });
    </script>
    <?php endif; ?>

    <script>
        // Bouton retour en haut : apparaît après 300px de scroll
        const btnTop = document.getElementById('btn-top');
        window.addEventListener('scroll', () => {
            btnTop.style.display = window.scrollY > 300 ? 'flex' : 'none';
            btnTop.style.alignItems = 'center';
            btnTop.style.justifyContent = 'center';
        });
    </script>

</body>
</html>