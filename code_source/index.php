<?php

	require_once 'config.php';
    require_once UTILISATEUR_CLASS_PROJET;
    require_once ALERTES_CLASS_PROJET;

    $alertes = new Alertes();

	//on attend un envois du formulaire et on prevois l'eventualiter ou les casses serais vide pour ne pas afficher le message a l'entrer de la page
	if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['login'], $_GET['mdp']))
	{
		if (empty($_GET['login']) || empty($_GET['mdp'])) // Si au moins un des champs n'est pas remplis
		{
			echo $alertes->alert_danger("Au moins un des champs est vide");
		}

		else
		{
			$connexion = new Utilisateur();
			$connexion->connexion_utilisateur($_GET['login'], $_GET['mdp']);
            echo $alertes->alert_danger($connexion->messages);
		}
	}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
<meta charset="UTF-8">
<title>Supervision chambres froides</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="style.css">

</head>

<body class="bg-light">

    <div class="container">

        <!-- Titre -->
        <div class="text-center mt-5 mb-4">
            <h1 class="fw-bold">Système de supervision</h1>
            <h3 class="text-muted fw-bold">Chambres froides</h3>
            <!-- <img src="./images/mendez_logo.png" alt="Logo" class="image-logo"> -->
        </div>

        <!-- Formulaire -->
        <div class="d-flex justify-content-center">

            <div class="card shadow login-card">

                <div class="card-header text-center bg-primary text-white">
                    Connexion utilisateur
                </div>

                <div class="card-body">

                    <form action="" method="GET">

                        <div class="mb-3">
                            <label class="form-label">Nom d'utilisateur</label>
                            <div class="input-group">

                                <span class="input-group-text">
                                    <img src="./images/bust_in_silhouette.png" alt="Utilisateur" class="images-login-card">
                                </span>

                                <input type="text" class="form-control" name="login" placeholder="Utilisateur" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mot de passe</label>
                            <div class="input-group">

                                <span class="input-group-text">
                                    <img src="./images/lock.png" alt="Cadenas" class="images-login-card"/>
                                </span>

                                <input type="password" class="form-control" name="mdp" placeholder="Mot de passe" required>
                            </div>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary">
                                Se connecter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
    <p class="text-center text-secondary mt-3">
        Lycée Pierre Mendès France - Épinal
    </p>
</body>

</html>