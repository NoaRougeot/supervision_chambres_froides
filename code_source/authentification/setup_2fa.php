<?php
    require_once __DIR__ . '/../config.php';
    require_once PROTECTION_CLASS_PROJET;
    require_once BDD_CLASS_PROJET;
    require_once AUTH_CLASS_PROJET;

    $protection = new Protection();
    $protection->url_protection();  // redirige si non connecté
    $protection->tfa_url_protection();
    $protection->already_tfa();

    $double_authentification = new DoubleAuthentification();

    // Initialisation des variables d'état
    $success = false;
    $erreur  = '';

    // Traitement du formulaire
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET['code']))
    {
        $code = trim($_GET['code']);

        if ($double_authentification->valider_code_2fa($code)) 
        {
            $success = true;
            $_SESSION["2fa_validated"] = True;
        }

        else
        {
            $erreur = 'Code invalide ou expiré. Réessayez.';
        }
    }

    // Bouton régénérer QR
    if (isset($_GET['regen'])) 
    {
        $double_authentification->regenerer_2fa();
    }

    // Génération / récupération du QR
    $qr_code = $double_authentification->initialiser_2fa();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Configurer la double authentification</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="bg-light">

    <!-- Navbar identique à espace_personnel -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">Mise en place 2FA</a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text text-light me-3">
                    <i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION['prenom'] ?? 'Utilisateur') ?>
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
                        <h4><i class="fas fa-shield-alt"></i> Configuration Double Authentification (2FA)</h4>
                    </div>

                    <div class="card-body">

                        <?php if ($success): ?>
                            <!-- Message de succès -->
                            <div class="alert alert-success text-center py-5">
                                <i class="fas fa-check-circle fa-4x mb-4 text-success"></i>
                                <h4>Double authentification activée avec succès !</h4>
                                <p class="lead">Votre compte est maintenant mieux protégé.</p>
                                <a href="../" class="btn btn-primary btn-lg mt-3">
                                    <i class="fas fa-arrow-left"></i> Retour à la page d'accueil
                                </a>
                            </div>
                        <?php else: ?>
                            <!-- Contenu normal : QR + formulaire -->
                            <p class="lead mb-4">
                                Scannez ce QR code avec votre application d'authentification 
                                (<strong>Google Authenticator</strong>, <strong>Authy</strong>, <strong>Microsoft Authenticator</strong>, etc.).
                            </p>

                            <?php if ($erreur): ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <?= htmlspecialchars($erreur) ?>
                                </div>
                            <?php endif; ?>

                            <div class="text-center my-5">
                                <img src="<?= htmlspecialchars($qr_code) ?>" 
                                     alt="QR Code pour 2FA" 
                                     class="img-fluid shadow" 
                                     style="max-width: 280px; border-radius: 12px;">
                            </div>

                            <div class="alert alert-info small mb-4">
                                <strong>Clé secrète manuelle (si vous ne pouvez pas scanner) :</strong><br>
                                <code class="font-monospace"><?= htmlspecialchars($_SESSION['cle_secrete_tempo'] ?? '—') ?></code>
                            </div>

                            <hr class="my-4">

                            <form method="GET">
                                <h5 class="text-success mb-3">
                                    <i class="fas fa-key me-2"></i> Entrez le code à 6 chiffres généré par votre application
                                </h5>

                                <div class="input-group input-group-lg mb-4 justify-content-center">
                                    <span class="input-group-text"><i class="fas fa-shield-halved"></i></span>
                                    <input type="text" 
                                           name="code" 
                                           class="form-control text-center" 
                                           placeholder="123456" 
                                           maxlength="6" 
                                           pattern="\d{6}" 
                                           required 
                                           autofocus 
                                           inputmode="numeric">
                                </div>

                                <button type="submit" class="btn btn-success btn-lg w-100">
                                    <i class="fas fa-check me-2"></i> Valider et activer
                                </button>
                            </form>

                            <div class="text-center mt-4">
                                <a href="?regen=1" class="text-muted small">
                                    <i class="fas fa-sync-alt me-1"></i> Générer un nouveau QR code
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="card-footer text-end">
                        <a href="../espaces_perso/espace_personnel.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Retour
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>