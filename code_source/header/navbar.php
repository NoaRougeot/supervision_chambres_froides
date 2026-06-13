<?php
/**
 * Navbar partagée.
 * Variables attendues avant l'include :
 *  - $titre_nav       (string)  : titre affiché dans le brand
 *  - $icone_nav       (string, optionnel) : classe FontAwesome ex: "fas fa-cog"
 *  - $lien_deconnexion (string, optionnel) : chemin relatif vers deconnexion.php
 */
$icone_nav ??= '';
$lien_deconnexion ??= '../authentification/deconnexion.php';
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand fw-bold">
            <?php if ($icone_nav): ?>
                <i class="<?= htmlspecialchars($icone_nav) ?> me-2"></i>
            <?php endif; ?>
            <?= htmlspecialchars($titre_nav ?? 'Supervision') ?>
        </a>
        <div class="navbar-nav ms-auto">
            <span class="navbar-text text-light me-3">
                <i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION['prenom'] ?? 'Utilisateur') ?>
            </span>
            <a href="<?= $lien_deconnexion ?>" class="btn btn-outline-danger btn-sm">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
        </div>
    </div>
</nav>
