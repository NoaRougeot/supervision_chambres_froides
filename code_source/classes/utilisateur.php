<?php

	require_once __DIR__ . '/../config.php';
    require_once BDD_CLASS_PROJET;

    class Utilisateur
    {
        private $bdd;
		private $bdd_requete = "SELECT * FROM Utilisateur WHERE pseudo = :login";
		public $messages = '';

        public function __construct()
        {
            $this->bdd = new Base_de_donnee();
            $this->bdd->connexion();
        }

        function connexion_utilisateur(string $login, string $mdp)
        {
			if ($this->bdd->est_connecter !== True)
			{
				$this->messages = "Connexion impossible a la base de donnée";
				return "Connexion impossible a la base de donnée";
			}

            else
            {
			    $result = $this->bdd->fetch($this->bdd_requete, [':login' => $login]);

			    if (!$result)
			    {
					$this->messages = "Identifiant ou mot de passe incorrect";
				    return "Identifiant ou mot de passe incorrect";
			    }

			    else
			    {
				    if (!password_verify($mdp, $result['hash_mdp']))
				    {
					    $this->messages = "Identifiant ou mot de passe incorrect";
				    	return "Identifiant ou mot de passe incorrect";
				    }

				    else
				    {
					    session_start();

						$_SESSION["logged"] = True;
						$_SESSION["2fa_validated"] = False;
					    $_SESSION["pseudo"] = $result["pseudo"];
					    $_SESSION["prenom"] = $result["prenom"];
					    $_SESSION["nom"] = $result["nom"];
					    $_SESSION["date_inscription"] = $result["date_inscription"];
					    $_SESSION["droits"] = $result["droits"];
					    $_SESSION["email"] = $result["email"];
					    $_SESSION["cle_secrete"] = $result["cle_secrete"];

					    if (empty($_SESSION["cle_secrete"]))
					    {
							header('Location: espaces_perso/espace_personnel.php');
                            exit;
					    }

					    else
					    {
						    header('Location: authentification/verif_2fa.php');
                            exit;
					    }
				    }
                }
            }
        }

		function deconnexion_utilisateur(bool $redirect = True)
		{
			$_SESSION=array();

    		if (ini_get("session.use_cookies"))
			{
        		$params = session_get_cookie_params();
        		setcookie(
        		session_name(),
        		'',
        		time() - 42000,
        		$params["path"],
        		$params["domain"],
        		$params["secure"],
        		$params["httponly"]
        		);

				session_destroy();
			}

			$this->bdd->deconnexion();

			if ($redirect)
			{
				header('Location: ../index.php');
    			exit;
			}
		}

		public function ajouter_utilisateur(): string
		{
    		if ($this->bdd->est_connecter !== True) 
			{
        		return "Connexion impossible à la base de données";
    		}

    		$pseudo = $_POST['pseudo'] ?? '';
    		$nom    = $_POST['nom']    ?? '';
    		$prenom = $_POST['prenom'] ?? '';
    		$droits = $_POST['droits'] ?? '';
    		$email  = $_POST['email']  ?? '';
    		$mdp    = $_POST['mdp']    ?? '';

    		if (empty($pseudo) || empty($nom) || empty($prenom) || empty($email) || empty($mdp)) 
			{
        		return "Tous les champs obligatoires doivent être remplis";
    		}

			// Vérification doublon pseudo
    		if ($this->bdd->fetch("SELECT id_utilisateur FROM Utilisateur WHERE pseudo = :pseudo", [':pseudo' => $pseudo]))
			{
        		return "Ce pseudo est déjà utilisé";
    		}

    		// Vérification doublon email
    		if ($this->bdd->fetch("SELECT id_utilisateur FROM Utilisateur WHERE email = :email", [':email' => $email]))
			{
        		return "Cet email est déjà associé à un compte";
    		}

    		$this->bdd->requetes_sql(
				"INSERT INTO Utilisateur (pseudo, nom, prenom, date_inscription, droits, hash_mdp, email, cle_secrete)
                VALUES (:pseudo, :nom, :prenom, :date_inscription, :droits, :hash_mdp, :email, '')",
				[
					':pseudo'           => $pseudo,
					':nom'              => $nom,
					':prenom'           => $prenom,
					':date_inscription' => time(),
					':droits'           => $droits,
					':hash_mdp'         => password_hash($mdp, PASSWORD_BCRYPT),
					':email'            => $email,
				]
			);

    		return "Utilisateur '$pseudo' ajouté avec succès";
		}

		function modifier_utilisateur(): string
		{
        	$id     = (int)($_POST['id_utilisateur'] ?? 0);
        	$pseudo = trim($_POST['pseudo'] ?? '');
        	$nom    = trim($_POST['nom']    ?? '');
        	$prenom = trim($_POST['prenom'] ?? '');
        	$email  = trim($_POST['email']  ?? '');
        	$droits = $_POST['droits'] ?? '';

        	if (!$id || !$pseudo || !$nom || !$prenom || !$email) 
        	{
           		return "Tous les champs obligatoires doivent être remplis.";
        	} 
        
        	// Vérification doublon pseudo
        	if ($this->bdd->fetch("SELECT id_utilisateur FROM Utilisateur WHERE pseudo = :p AND id_utilisateur != :id", [':p' => $pseudo, ':id' => $id]))
            {
                return "Ce pseudo est déjà utilisé.";
            }

        	// Vérification doublon email
        	if ($this->bdd->fetch("SELECT id_utilisateur FROM Utilisateur WHERE email = :e AND id_utilisateur != :id", [':e' => $email, ':id' => $id]))
            {
            	return "Cet email est déjà associé à un compte.";
            }

        	$mdp_raw = $_POST['mdp'] ?? '';

        	if (!empty($mdp_raw)) 
            {
                $this->bdd->requetes_sql("UPDATE Utilisateur SET pseudo=:pseudo, nom=:nom, prenom=:prenom, email=:email, droits=:droits, hash_mdp=:hash WHERE id_utilisateur=:id", [':pseudo'=>$pseudo, ':nom'=>$nom, ':prenom'=>$prenom, ':email'=>$email, ':droits'=>$droits, ':hash'=>password_hash($mdp_raw, PASSWORD_BCRYPT), ':id'=>$id]);
            } 
        
        	else 
            {
                $this->bdd->requetes_sql("UPDATE Utilisateur SET pseudo=:pseudo, nom=:nom, prenom=:prenom, email=:email, droits=:droits WHERE id_utilisateur=:id", [':pseudo'=>$pseudo, ':nom'=>$nom, ':prenom'=>$prenom, ':email'=>$email, ':droits'=>$droits, ':id'=>$id]);
            }

        	return "Utilisateur '$pseudo' modifié avec succès.";
    	}

		function supprimer_utilisateur(): string
		{
        	$id = (int)($_POST['id_utilisateur'] ?? 0);

        	if (!$id) 
			{
            	return "Identifiant invalide.";
        	} 

			$this->bdd->requetes_sql("DELETE FROM Utilisateur WHERE id_utilisateur = :id", [':id' => $id]);
        	return "Utilisateur supprimé.";
		}
	}
?>