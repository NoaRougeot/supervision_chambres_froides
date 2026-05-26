<?php
	require_once __DIR__ . '/../config.php';
    require_once PROTECTION_CLASS_PROJET;
    require_once UTILISATEUR_CLASS_PROJET;

    session_start();
    $protection = new Protection();
    $protection->url_protection();
    $utilisateur = new Utilisateur();
    $utilisateur->deconnexion_utilisateur();
?>