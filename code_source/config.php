<?php
    //on définit une fois pour toutes les chemins d'accès utiles aux différents fichiers propres au projet global

	define('RACINE_PROJET', realpath(__DIR__)); //niveaux le plus haut (racine)

	define('INDEX_PROJET', RACINE_PROJET . '/index.php');
	define('CONFIG_PROJET', RACINE_PROJET . '/config.php');

	define('CLASSES_PROJET', RACINE_PROJET . '/classes');
	define('BDD_CLASS_PROJET', CLASSES_PROJET . '/bdd.php');
	define('UTILISATEUR_CLASS_PROJET', CLASSES_PROJET . '/utilisateur.php');
	define('PROTECTION_CLASS_PROJET', CLASSES_PROJET . '/protection.php');
	define('AUTH_CLASS_PROJET', CLASSES_PROJET . '/double_authentification.php');
	define('CHAMBRE_FROIDES_CLASS_PROJET', CLASSES_PROJET . '/chambre_froides.php');
	define('ALERTES_CLASS_PROJET', CLASSES_PROJET . '/alertes.php');

	define('LOGS_PROJET', RACINE_PROJET . '/logs');
	define('BDD_LOGS_PROJET', LOGS_PROJET . '/bdd_connexions.log');
	define('URL_EXPLOIT_LOGS_PROJET', LOGS_PROJET . '/acces_interdits.log');

	define('INTERDICTION_PROJET', RACINE_PROJET . '/interdiction');
	define('URL_EXPLOIT_PROJET', INTERDICTION_PROJET . '/acces_interdit.php');

	define('GRAPH_PROJET', RACINE_PROJET . '/temperatures');
	define('TEMPS_PORTES_PROJET', GRAPH_PROJET . '/graph.php');

	define('GESTION_UTILISATEURS_CHAMBRES_PROJET', RACINE_PROJET . '/gestion_utilisateur_chambres');
	define('AJOUTER_UTILISATEURS_CHAMBRES_PROJET', GESTION_UTILISATEURS_CHAMBRES_PROJET . '/ajouter.php');

	define('CSS_UTILE', RACINE_PROJET . '/style');
	define('CSS_BOOTSTRAPS', CSS_UTILE . '/bootstrap/css/bootstrap.min.css');
	define('CSS_FONTAWESOME', CSS_UTILE . '/fontawesome/css/all.min.css');

	define('TFA_PROJET', RACINE_PROJET . '/vendor/autoload.php');

    //Format d'un mot de passe respectant le format de l'ANSSI
    $pattern_cfg = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{12,}$/';
	
	$serveur_cfg = "mysql-rougeot.alwaysdata.net";
    $utilisateur_cfg  = "rougeot";
    $mdp_cfg  = "NoNoDu8854450";
    $bdd_cible_cfg  = "rougeot_projet_bts";
?>