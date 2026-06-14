<?php
	require_once __DIR__ . '/../config.php';
    require_once BDD_CLASS_PROJET;

    class Utilisateur
    {
        private $bdd;
		private $bdd_requete = "SELECT * FROM Utilisateur WHERE pseudo = :login";
		public $messages = '';

        // Anti brute-force : constantes de seuil
        private const MAX_TENTATIVES  = 5;    // nb d'échecs avant blocage
        private const DELAI_BLOCAGE   = 300;  // secondes de blocage (5 min)

        public function __construct()
        {
            $this->bdd = new Base_de_donnee();
            $this->bdd->connexion();
        }

        // Anti brute-force (stockage en session, pas de BDD)

        /**
         * Retourne le nombre de secondes restantes de blocage (0 si pas bloqué).
         */
        private function secondes_restantes(): int
        {
            if (empty($_SESSION['bf_bloque_jusqu_a'])) 
			{
                return 0;
            }

            $reste = (int)$_SESSION['bf_bloque_jusqu_a'] - time();
            return max(0, $reste);
        }

        /**
         * Incrémente le compteur d'échecs et pose un blocage si le seuil est atteint.
         */
        private function enregistrer_echec(): void
        {
            $_SESSION['bf_tentatives']  = ($_SESSION['bf_tentatives']  ?? 0) + 1;
            $_SESSION['bf_derniere_ip'] = $_SERVER['REMOTE_ADDR'] ?? 'inconnue';

            if ($_SESSION['bf_tentatives'] >= self::MAX_TENTATIVES) 
			{
                $_SESSION['bf_bloque_jusqu_a'] = time() + self::DELAI_BLOCAGE;

                // Log
                error_log(
                    "[BRUTE-FORCE] " . date('Y-m-d H:i:s') . " – IP: " .
                    ($_SERVER['REMOTE_ADDR'] ?? '?') . " – Login: bloqué après " .
                    self::MAX_TENTATIVES . " tentatives\n",
                    3,
                    BRUTEFORCE_LOGS_PROJET 
                );
            }
        }

        /**
         * Réinitialise les compteurs après une connexion réussie.
         */
        private function reinitialiser_compteur(): void
        {
            unset($_SESSION['bf_tentatives'], $_SESSION['bf_bloque_jusqu_a'], $_SESSION['bf_derniere_ip']);
        }

        // Connexion principale
        function connexion_utilisateur(string $login, string $mdp)
        {
            // 1. Vérification du blocage actif
            $reste = $this->secondes_restantes();

            if ($reste > 0) 
			{
                $minutes = ceil($reste / 60);
                $this->messages = "Trop de tentatives échouées. Compte temporairement bloqué. Réessayez dans {$minutes} minute(s).";
                return $this->messages;
            }

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
                    $this->enregistrer_echec();
					$this->messages = "Identifiant ou mot de passe incorrect";
				    return "Identifiant ou mot de passe incorrect";
			    }

			    else
			    {
				    if (!password_verify($mdp, $result['hash_mdp']))
				    {
                        $this->enregistrer_echec();
					    $this->messages = "Identifiant ou mot de passe incorrect";
				    	return "Identifiant ou mot de passe incorrect";
				    }

				    else
				    {
                        // Connexion réussie reset compteur
                        $this->reinitialiser_compteur();

						$_SESSION["logged"] = True;
						$_SESSION["2fa_validated"] = False;

						$_SESSION["id_utilisateur"] = $result["id_utilisateur"]; // pour la modif de son propre utilisateur dans la méthode modifier_utilisateur()
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

        	if ($id <= 0 || !$pseudo || !$nom || !$prenom || !$email) 
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

			if (isset($_SESSION['id_utilisateur']) && (int)$_SESSION['id_utilisateur'] === $id) 
			{
    			$_SESSION['pseudo']  = $pseudo;
    			$_SESSION['nom']     = $nom;
    			$_SESSION['prenom']  = $prenom;
    			$_SESSION['email']   = $email;
    			$_SESSION['droits']  = $droits;

				// Si le mdp a été changé et que c'est l'utilisateur connecté, on le déconnecte
    			if (!empty($mdp_raw)) 
				{
        			$this->deconnexion_utilisateur();
    			}
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