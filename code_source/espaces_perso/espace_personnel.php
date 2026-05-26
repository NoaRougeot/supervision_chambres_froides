<?php
    require_once __DIR__ . '/../config.php';
    require_once PROTECTION_CLASS_PROJET;

    session_start();
    $protection = new Protection();
    $protection->url_protection();
    $protection->tfa_url_protection();

    $statut = "";

    if ($_SESSION['droits'] == 'admin')
    {
        $statut = "Technicien";
    }

    else
    {
        $statut = "Chef de services";
    }
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
    </style>
</head>

<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">Mon Espace</a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text text-light me-3">
                    <i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION['prenom']) ?>
                </span>
                <a href="../authentification/deconnexion.php" class="btn btn-outline-danger btn-sm">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow">
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
                                    <li class="list-group-item"><strong>Date de creation du compte :</strong> <?= htmlspecialchars(date("d-m-Y H:i:s", $_SESSION['date_inscription'])) ?></li>
                                    <li class="list-group-item"><strong>Droits :</strong> <span class="badge bg-secondary"><?php echo $statut ?></span></li>
                                </ul>
                            </div>

                            <div class="col-md-5">
                                <h5 class="text-success">Double authentification (2FA)</h5>
                                <div class="p-3 border rounded bg-light">

                                    <?php if (!empty($_SESSION['cle_secrete'])): ?>
                                        <p class="text-success mb-0"><i class="fas fa-check-circle fa-2x"></i><br>Activée et sécurisée</p>
                                        <a href="../authentification/desactivation_2fa.php" class="btn btn-danger btn-sm">Desactiver 2fa</a>
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
</body>
</html>