<?php

require_once __DIR__ . '/../config.php';
session_start();

// ==================== CAPTURE ET LOG DE L'INTRUSION ====================
$ip_visiteur     = $_SERVER['REMOTE_ADDR'] ?? 'IP inconnue';
$heure           = date('d/m/Y à H:i:s');
$user_agent      = $_SERVER['HTTP_USER_AGENT'] ?? 'Inconnu';
$page_tentee     = $_SERVER['HTTP_REFERER'] ?? 'Page inconnue';

$log_message = "[$heure] TENTATIVE D'INTRUSION - IP: $ip_visiteur | User-Agent: $user_agent | Page tentée: $page_tentee\n";

// Enregistrement dans le log (format identique à tes logs BDD)
file_put_contents(URL_EXPLOIT_LOGS_PROJET, $log_message, FILE_APPEND | LOCK_EX);
// =====================================================================
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accès Refusé - Authentification Requise</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="style.css">

</head>
<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-md-9">
            <div class="card warning-card text-center p-5">
                <div class="card-body">
                    
                    <i class="fas fa-lock icon-lock mb-4"></i>
                    
                    <h1 class="display-5 fw-bold text-danger mb-3">
                        ACCÈS REFUSÉ
                    </h1>
                    
                    <h4 class="text-muted mb-4">Authentification requise</h4>
                    
                    <!-- Infos techniques claires -->
                    <div class="alert alert-danger border-danger mb-4">
                        <strong>Votre adresse IP a été enregistrée :</strong><br>
                        <span class="fs-5 fw-bold"><?= htmlspecialchars($ip_visiteur) ?></span>
                    </div>

                    <p class="lead mb-4">
                        Tentative d’accès non autorisée détectée le <strong><?= $heure ?></strong><br>
                        <small class="text-muted">Navigateur : <?= htmlspecialchars($user_agent) ?></small>
                    </p>

                    <div class="border border-danger p-4 mb-4 text-start bg-white">
                        <strong>Information importante :</strong><br><br>
                        • Cette tentative est enregistrée dans les logs de sécurité.<br>
                        • Toute nouvelle tentative sans authentification sera tracée.<br>
                        • Nous conservons les preuves en cas de besoin.
                    </div>

                    <a href="../index.php" class="btn btn-danger btn-lg px-5 py-3 shadow-sm">
                        <i class="fas fa-arrow-left"></i> 
                        RETOUR À L’ACCUEIL
                    </a>
                </div>
            </div>

            <p class="text-center mt-4 text-muted small">
                Projet BTS - Chambre froide
            </p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>