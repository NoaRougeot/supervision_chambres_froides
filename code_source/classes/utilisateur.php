<?php

	require_once __DIR__ . '/../config.php';
    require_once BDD_CLASS_PROJET;

    class Utilisateur
    {
        private $bdd;
		private $bdd_requete = "SELECT * FROM Utilisateur WHERE pseudo = :login"; // si le login est contenu en base de données
		public $messages = '';

        // Constructeur pour initialiser la BDD
        public function __construct()
        {
            $this->bdd = new Base_de_donnee();
            $this->bdd->connexion(); // Établis la connexion une fois pour toutes
        }

        function connexion_utilisateur(string $login, string $mdp)
        {
            // si on n'est pas connecté à la base de données
			if ($this->bdd->est_connecter !== True)
			{
				$this->messages = "Connexion impossible a la base de donnée";
				return "Connexion impossible a la base de donnée";
			}

            else
            {
			    $result = $this->bdd->fetch($this->bdd_requete, [':login' => $login]); //on crée un tableau associatif avec les infos associées à l'identifiant utilisateur en base de données

			    if (!$result)
			    {
					$this->messages = "Identifiant ou mot de passe incorrect";
				    return "Identifiant ou mot de passe incorrect";
			    }

			    else
			    {
				    //on vérifie si le hash du mot de passe saisi est le même que celui déjà présent en base de données
				    if (!password_verify($mdp, $result['hash_mdp']))
				    {
					    $this->messages = "Identifiant ou mot de passe incorrect";
				    	return "Identifiant ou mot de passe incorrect";
				    }

				    else
				    {
					    session_start(); // on crée une session PHP où on stockera chaque champ du tableau

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
			$_SESSION=array(); // on stocke toutes les variables de session dans un tableau vide pour libérer l'espace mémoire

			// Supprimer le cookie PHPSESSID s'il existe
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

				session_destroy(); // on détruit la session
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

    		$pseudo    = $_GET['pseudo']    ?? '';
    		$nom       = $_GET['nom']       ?? '';
    		$prenom    = $_GET['prenom']    ?? '';
    		$droits    = $_GET['droits']    ?? '';
    		$email     = $_GET['email']     ?? '';
    		$mdp       = $_GET['mdp']       ?? '';

    		if (empty($pseudo) || empty($nom) || empty($prenom) || empty($email) || empty($mdp)) 
			{
        		return "Tous les champs obligatoires doivent être remplis";
    		}

			// Vérification doublon pseudo
    		$check_pseudo = $this->bdd->pdo->prepare("SELECT id_utilisateur FROM Utilisateur WHERE pseudo = :pseudo");
    		$check_pseudo->execute([':pseudo' => $pseudo]);

    		if ($check_pseudo->fetch()) 
			{
        		return "Ce pseudo est déjà utilisé";
    		}

    		// Vérification doublon email
    		$check_email = $this->bdd->pdo->prepare("SELECT id_utilisateur FROM Utilisateur WHERE email = :email");
    		$check_email->execute([':email' => $email]);

    		if ($check_email->fetch()) 
			{
        		return "Cet email est déjà associé à un compte";
    		}

    		$hash = password_hash($mdp, PASSWORD_BCRYPT);
    		$timestamp = time();

    		$requete = "INSERT INTO Utilisateur (pseudo, nom, prenom, date_inscription, droits, hash_mdp, email, cle_secrete)
                		VALUES (:pseudo, :nom, :prenom, :date_inscription, :droits, :hash_mdp, :email, '')";

    		$stmt = $this->bdd->pdo->prepare($requete);
    		$stmt->execute([
        		':pseudo'           => $pseudo,
        		':nom'              => $nom,
        		':prenom'           => $prenom,
        		':date_inscription' => $timestamp,
        		':droits'           => $droits,
        		':hash_mdp'         => $hash,
        		':email'            => $email,
    		]);

    		return "Utilisateur '$pseudo' ajouté avec succès";
		}

		function modifier_utilisateur(): string
		{
        	$id = (int)($_GET['id_utilisateur'] ?? 0);
        	$pseudo = trim($_GET['pseudo'] ?? '');
        	$nom = trim($_GET['nom'] ?? '');
        	$prenom = trim($_GET['prenom'] ?? '');
        	$email = trim($_GET['email'] ?? '');
        	$droits = $_GET['droits'] ?? '';

        	if (!$id || !$pseudo || !$nom || !$prenom || !$email) 
        	{
           		return "Tous les champs obligatoires doivent être remplis.";
        	} 
        
        	else 
        	{
            	$chk = $bdd->pdo->prepare("SELECT id_utilisateur FROM Utilisateur WHERE pseudo=:p AND id_utilisateur!=:id");
            	$chk->execute([':p'=>$pseudo,':id'=>$id]);

            	if ($chk->fetch()) 
            	{
                	return "Ce pseudo est déjà utilisé.";
            	} 
            
            	else 
                {
                    $chk2 = $bdd->pdo->prepare("SELECT id_utilisateur FROM Utilisateur WHERE email=:e AND id_utilisateur!=:id");
                    $chk2->execute([':e'=>$email,':id'=>$id]);

                    if ($chk2->fetch()) 
                    {
                    	return "Cet email est déjà associé à un compte.";
                    } 
                
                    else 
                    {
                        $mdp_raw = $_GET['mdp'] ?? '';

                        if (!empty($mdp_raw)) 
                        {
                            $hash = password_hash($mdp_raw, PASSWORD_BCRYPT);

                            $stmt = $bdd->pdo->prepare(
                                "UPDATE Utilisateur SET pseudo=:pseudo,nom=:nom,prenom=:prenom,email=:email,droits=:droits,hash_mdp=:hash
                                WHERE id_utilisateur=:id"
                                );
                            $stmt->execute([':pseudo'=>$pseudo,':nom'=>$nom,':prenom'=>$prenom,':email'=>$email,':droits'=>$droits,':hash'=>$hash,':id'=>$id]);
                        } 
                    
                        else 
                        {
                            $stmt = $bdd->pdo->prepare(
                                "UPDATE Utilisateur SET pseudo=:pseudo,nom=:nom,prenom=:prenom,email=:email,droits=:droits
                                WHERE id_utilisateur=:id"
                            );
                            $stmt->execute([':pseudo'=>$pseudo,':nom'=>$nom,':prenom'=>$prenom,':email'=>$email,':droits'=>$droits,':id'=>$id]);
                        }

                        return "Utilisateur '$pseudo' modifié avec succès.";
                	}
            	}
        	}
    	}

		function suprimer_utilisateur(): string
		{
        	$id = (int)($_GET['id_utilisateur'] ?? 0);

        	if (!$id) 
			{
            	$message_user = "Identifiant invalide.";
        	} 
			
			else 
			{
            	$stmt = $bdd->pdo->prepare("DELETE FROM Utilisateur WHERE id_utilisateur = :id");
            	$stmt->execute([':id' => $id]);
            	return "Utilisateur supprimé.";
        	}
		}
	}
?>