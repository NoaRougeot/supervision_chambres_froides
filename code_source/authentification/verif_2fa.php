<?php
require_once __DIR__ . '/../config.php';
require_once PROTECTION_CLASS_PROJET;
require_once BDD_CLASS_PROJET;
require_once AUTH_CLASS_PROJET;

$protection = new Protection();
$protection->url_protection();   // redirige si pas connecté
$protection->not_tfa();

$double_authentification = new DoubleAuthentification();
$erreur = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['code']))
{
    $code = trim($_POST['code']);

    // Récupère la clé PERMANENTE depuis la session
    $secret = $_SESSION['cle_secrete'] ?? null;

    if (!$secret) 
    {
        // Sécurité : si on n'a plus la clé en session → on force la déconnexion
        header("Location: ../authentification/deconnexion.php");
        exit;
    }

    // Vérification TOTP avec tolérance de 1 période
    $isValid = $double_authentification->verifier_totp($code);   // ← on appelle directement la lib

    if ($isValid)
    {
        // Marque comme validé pour cette session
        $_SESSION['2fa_validated'] = True;

        header("Location: ../espaces_perso/espace_personnel.php");
        exit;
    } 
    
    else 
    {
        $erreur = "Code invalide ou expiré. Veuillez réessayer.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vérification Double Authentification</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">Sécurité</a>
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
            <div class="col-lg-6 col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h4><i class="fas fa-shield-halved"></i> Vérification en deux étapes</h4>
                    </div>

                    <div class="card-body text-center">
                        <p class="lead mb-4">
                            Entrez le code à 6 chiffres généré par votre application d'authentification
                        </p>

                        <?php if ($erreur): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?= htmlspecialchars($erreur) ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="input-group input-group-lg mb-4 justify-content-center mx-auto" style="max-width: 320px;">
                                <span class="input-group-text"><i class="fas fa-key"></i></span>
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
                                <i class="fas fa-check me-2"></i> Vérifier
                            </button>
                        </form>

                        <div class="mt-4 small text-muted">
                            <p>Aplication perdu ? <a href="../authentification/deconnexion.php">Déconnexion</a></p>
                        </div>
                    </div>

                    <div class="card-footer text-center">
                        <small>Chambre Froides – Sécurité renforcée</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>