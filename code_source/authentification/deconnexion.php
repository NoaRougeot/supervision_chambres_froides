<?php
require_once __DIR__ . '/../config.php';
require_once UTILISATEUR_CLASS_PROJET;

session_start();

// Pas besoin de protection ici : si on est pas connecté
// on déconnecte quand même et on redirige vers l'index, c'est safe
$utilisateur = new Utilisateur();
$utilisateur->deconnexion_utilisateur();
?>