<?php
    session_start();
    require_once __DIR__ . '/../config.php';
    require_once PROTECTION_CLASS_PROJET;
    require_once CHAMBRE_FROIDES_CLASS_PROJET;

    $protection = new Protection();
    $protection->url_protection();
    $protection->tfa_url_protection();

    $titre_nav = 'Mon Espace';

    require_once NAVBAR;

    $statut = "";

    if ($_SESSION['droits'] == 'admin')
    {
        $statut = "Technicien";
    }
    else
    {
        $statut = "Chef de services";
    }

    // Récupération de la liste des chambres pour le sélecteur
    $chambre_froides = new Chambre_froides();
    $liste_chambres  = $chambre_froides->info_chambres();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mon Espace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .card-hover {
            transition: transform 0.15s ease, box-shadow 0.15s ease;
            cursor: pointer;
        }
        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
        }

        /* Indicateur de connexion SSE */
        #sse-statut {
            font-size: .75rem;
        }
        .sse-connecte  { color: #198754; }
        .sse-attente   { color: #ffc107; }
        .sse-erreur    { color: #dc3545; }

        /* Badge alerte non acquittée */
        .badge-alerte-active { animation: clignoter 1.2s step-start infinite; }
        @keyframes clignoter { 50% { opacity: 0; } }
    </style>
</head>

<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-9">

                <!-- Bloc état temps réel des chambres -->
                <div class="card shadow mb-4">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-satellite-dish me-2"></i>État en temps réel</h5>
                        <span id="sse-statut" class="sse-attente d-none"></span>
                    </div>

                    <div class="card-body">

                        <!-- Sélecteur de chambre -->
                        <div class="mb-3">
                            <label for="select-chambre" class="form-label fw-bold">
                                <i class="fas fa-snowflake me-1 text-info"></i>Choisir une chambre froide
                            </label>
                            <select id="select-chambre" class="form-select">
                                <option value="">— Sélectionnez une chambre —</option>
                                <?php foreach ($liste_chambres as $chambre): ?>
                                    <option value="<?= (int)$chambre['id_chambre'] ?>">
                                        <?= htmlspecialchars($chambre['nom_chambre']) ?>
                                        (<?= $chambre['type_chambre'] ? 'Chambre positive' : 'Chambre negative' ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Panneau de données (masqué tant qu'aucune chambre sélectionnée) -->
                        <div id="panneau-donnees" class="d-none">
                            <div class="row g-3 text-center">

                                <!-- Température -->
                                <div class="col-md-4">
                                    <div class="card h-100 border-info">
                                        <div class="card-body">
                                            <i class="fas fa-thermometer-half fa-2x text-info mb-2"></i>
                                            <h6 class="text-muted">Dernière température</h6>
                                            <div id="val-temp" class="fs-3 fw-bold text-info">—</div>
                                            <small id="horo-temp" class="text-muted"></small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Porte -->
                                <div class="col-md-4">
                                    <div class="card h-100 border-warning">
                                        <div class="card-body">
                                            <i class="fas fa-door-open fa-2x text-warning mb-2"></i>
                                            <h6 class="text-muted">État de la porte</h6>
                                            <div id="val-porte" class="fs-4 fw-bold">—</div>
                                            <small id="horo-porte" class="text-muted"></small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Alerte -->
                                <div class="col-md-4">
                                    <div class="card h-100 border-danger">
                                        <div class="card-body">
                                            <i class="fas fa-bell fa-2x text-danger mb-2"></i>
                                            <h6 class="text-muted">Dernière alerte</h6>
                                            <div id="val-alerte" class="fs-6 fw-bold">—</div>
                                            <div id="badge-alerte" class="mt-1"></div>
                                            <small id="horo-alerte" class="text-muted"></small>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <p class="text-muted text-end small mt-2 mb-0">
                                <i class="fas fa-sync-alt me-1"></i>Mise à jour automatique toutes les 2s
                            </p>
                        </div>

                        <!-- Message si aucune chambre -->
                        <div id="message-vide" class="text-center text-muted py-3">
                            <i class="fas fa-arrow-up me-1"></i>Sélectionnez une chambre pour voir ses données en direct.
                        </div>
                    </div>
                </div>

                <!-- fin bloc temps réel -->

                <!-- ── Informations utilisateur ── -->
                <div class="card shadow mb-4">
                    <div class="card-header bg-primary text-white">
                        <h4><i class="fas fa-home"></i> Bienvenue dans votre espace personnel</h4>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-7">
                                <h5 class="text-primary">Vos informations</h5>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item"><strong>Pseudo :</strong> <?= htmlspecialchars($_SESSION['pseudo']) ?></li>
                                    <li class="list-group-item"><strong>Nom complet :</strong> <?= htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom']) ?></li>
                                    <li class="list-group-item"><strong>Email :</strong> <?= htmlspecialchars($_SESSION['email']) ?></li>
                                    <li class="list-group-item"><strong>Date de création du compte :</strong> <?= htmlspecialchars(date("d-m-Y H:i:s", $_SESSION['date_inscription'])) ?></li>
                                    <li class="list-group-item"><strong>Droits :</strong> <span class="badge bg-secondary"><?php echo $statut ?></span></li>
                                </ul>
                            </div>

                            <div class="col-md-5">
                                <h5 class="text-success">Double authentification (2FA)</h5>
                                <div class="p-3 border rounded bg-light">
                                    <?php if (!empty($_SESSION['cle_secrete'])): ?>
                                        <p class="text-success mb-0"><i class="fas fa-check-circle fa-2x"></i><br>Activée et sécurisée</p>
                                        <a href="../authentification/desactivation_2fa.php" class="btn btn-danger btn-sm">Désactiver 2FA</a>
                                    <?php else: ?>
                                        <p class="text-warning"><i class="fas fa-exclamation-triangle"></i> Non configurée</p>
                                        <a href="../authentification/setup_2fa.php" class="btn btn-warning btn-sm">Configurer maintenant</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Cards de navigation -->
                        <hr class="mt-4">
                        <h5 class="text-primary mb-3"><i class="fas fa-th-large"></i> Tableau de bord</h5>

                        <div class="row g-3">
                            <?php if ($_SESSION['droits'] == 'admin'): ?>
                                <div class="col-md-4">
                                    <a href="../temperatures/graph.php" class="text-decoration-none">
                                        <div class="card h-100 border-primary text-center p-3 card-hover">
                                            <div class="card-body">
                                                <i class="fas fa-temperature-low fa-2x text-primary mb-2"></i>
                                                <h6 class="card-title fw-bold">Supervision</h6>
                                                <p class="card-text text-muted small">Surveiller les chambres froides en temps réel</p>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <a href="../gestion_utilisateur_chambres/ajouter.php" class="text-decoration-none">
                                        <div class="card h-100 border-success text-center p-3 card-hover">
                                            <div class="card-body">
                                                <i class="fas fa-plus-circle fa-2x text-success mb-2"></i>
                                                <h6 class="card-title fw-bold">Ajout de données</h6>
                                                <p class="card-text text-muted small">Ajouter des utilisateurs ou des chambres froides</p>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="col-md-4">
                                    <a href="../temperatures/graph.php" class="text-decoration-none">
                                        <div class="card h-100 border-primary text-center p-3 card-hover">
                                            <div class="card-body">
                                                <i class="fas fa-temperature-low fa-2x text-primary mb-2"></i>
                                                <h6 class="card-title fw-bold">Supervision</h6>
                                                <p class="card-text text-muted small">Consulter l'état des chambres froides</p>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    /**
     * Gestion SSE (Server-Sent Events) pour l'état temps réel des chambres.
     */

    let eventSource = null;

    // Helpers 

    function formaterDate(timestamp) 
    {
        if (!timestamp) return '';
        const d = new Date(timestamp * 1000);

        return d.toLocaleString('fr-FR', 
        {
            day:    '2-digit', month: '2-digit', year: 'numeric',
            hour:   '2-digit', minute: '2-digit', second: '2-digit'
        });
    }

    function setStatut(etat) 
    {
        const el = document.getElementById('sse-statut');

        if (etat === 'ok') 
        {
            el.className = 'sse-connecte';
            el.innerHTML = '<i class="fas fa-circle me-1"></i>Connecté – flux actif';
        } 
        
        else if (etat === 'attente') 
        {
            el.className = 'sse-attente';
            el.innerHTML = '<i class="fas fa-circle-notch fa-spin me-1"></i>Connexion…';
        } 
        
        else 
        {
            el.className = 'sse-erreur';
            el.innerHTML = '<i class="fas fa-exclamation-circle me-1"></i>Erreur de connexion';
        }
    }

    // Ouverture / fermeture du flux SSE

    function ouvrirSSE(idChambre) 
    {
        if (eventSource) 
        {
            eventSource.close();
            eventSource = null;
        }

        setStatut('attente');
        document.getElementById('sse-statut').classList.remove('d-none');
        document.getElementById('panneau-donnees').classList.add('d-none');
        document.getElementById('message-vide').classList.remove('d-none');

        if (!idChambre) {
            document.getElementById('sse-statut').classList.add('d-none');
            return;
        }

        eventSource = new EventSource('donnees_chambre_sse.php?id=' + encodeURIComponent(idChambre));

        eventSource.addEventListener('maj_chambre', function(e) 
        {
            const data = JSON.parse(e.data);
            setStatut('ok');
            document.getElementById('panneau-donnees').classList.remove('d-none');
            document.getElementById('message-vide').classList.add('d-none');
            mettreAJourUI(data);
        });

        eventSource.addEventListener('erreur', function(e) 
        {
            setStatut('erreur');
        });

        eventSource.onerror = function() 
        {
            setStatut('erreur');
        };
    }

    // Mise à jour du DOM 

    function mettreAJourUI(data) {

        // Température 
        const divTemp  = document.getElementById('val-temp');
        const horoTemp = document.getElementById('horo-temp');

        if (data.temperature) 
        {
            divTemp.textContent  = data.temperature.valeur + ' °C';
            horoTemp.textContent = formaterDate(data.temperature.horodatage);
        } 
        else 
        {
            divTemp.textContent  = 'Aucune donnée';
            horoTemp.textContent = '';
        }

        // Porte
        const divPorte  = document.getElementById('val-porte');
        const horoPorte = document.getElementById('horo-porte');

        if (data.porte !== null && data.porte !== undefined) 
        {
            const ouverte = data.porte.etat === 1;
            divPorte.innerHTML  = ouverte
                ? '<span class="text-danger"><i class="fas fa-door-open me-1"></i>Ouverte</span>'
                : '<span class="text-success"><i class="fas fa-door-closed me-1"></i>Fermée</span>';
            horoPorte.textContent = formaterDate(data.porte.horodatage);
        } 
        else 
        {
            divPorte.textContent  = 'Aucune donnée';
            horoPorte.textContent = '';
        }

        // --- Alerte ---
        const divAlerte  = document.getElementById('val-alerte');
        const badgeAlerte = document.getElementById('badge-alerte');
        const horoAlerte = document.getElementById('horo-alerte');

        if (data.alerte) 
        {
            // Traduction logique du type
            let typeLabel = 'Type inconnu';
            if (data.alerte.type === 0) {
                typeLabel = 'Seuil minimum dépassé';
            } else if (data.alerte.type === 1) {
                typeLabel = 'Seuil maximum dépassé';
            } else if (data.alerte.type === 2) {
                typeLabel = 'Porte ouverte trop longtemps';
            }
            
            divAlerte.textContent = typeLabel;

            if (data.alerte.acquittee) 
            {
                badgeAlerte.innerHTML = '<span class="badge bg-success">Acquittée</span>';
            } 
            else 
            {
                badgeAlerte.innerHTML = '<span class="badge bg-danger badge-alerte-active"><i class="fas fa-exclamation me-1"></i>Non acquittée</span>';
            }

            horoAlerte.textContent = formaterDate(data.alerte.horodatage);
        } 
        else 
        {
            divAlerte.textContent  = 'Aucune alerte';
            badgeAlerte.innerHTML  = '';
            horoAlerte.textContent = '';
        }
    }

    // Écoute du sélecteur
    document.getElementById('select-chambre').addEventListener('change', function() 
    {
        ouvrirSSE(this.value);
    });

    // Ferme proprement la connexion quand l'utilisateur quitte la page
    window.addEventListener('beforeunload', function() 
    {
        if (eventSource) eventSource.close();
    });
    </script>
</body>
</html>