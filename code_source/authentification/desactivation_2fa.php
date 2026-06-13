<?php
    session_start();
    require_once __DIR__ . '/../config.php';
    require_once PROTECTION_CLASS_PROJET;
    require_once AUTH_CLASS_PROJET;

    $protection = new Protection();
    $protection->url_protection();
    $protection->tfa_url_protection();
    $protection->not_tfa();

    $tfa = new DoubleAuthentification();

    if ($tfa->desactiver_2fa())
    {
        header('Location: ../index.php');
        exit;
    }
?>