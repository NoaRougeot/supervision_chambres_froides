<?php

    require_once __DIR__ . '/../config.php';
    require_once CONFIG_PROJET;

    class Base_de_donnee
    {
        // Variables privées utilisées pour se connecter à la base de données
        private $serveur;
        private $utilisateur;
        private $mdp;
        private $bdd_cible;

        public $pdo = null;
        public $est_connecter = False;
        
        public function __construct()
        {
            global $serveur_cfg, $utilisateur_cfg, $mdp_cfg, $bdd_cible_cfg;

            $this->serveur     = $serveur_cfg;
            $this->utilisateur = $utilisateur_cfg;
            $this->mdp         = $mdp_cfg;
            $this->bdd_cible   = $bdd_cible_cfg;
        }
        
        /* Méthode de connexion à la base de données*/
        public function connexion()
        {
            try
            {
                // On vérifie si on est déjà connecté à la base de données
                if ($this->est_connecter != True)
                {
                    // Utilisation de pdo à la place de mysqli -> plus sécurisé et requêtes préparées natives
                    $this->pdo = new PDO("mysql:host={$this->serveur};dbname={$this->bdd_cible}",$this->utilisateur,$this->mdp,

                    [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES   => false,
                    ]);

                    $this->est_connecter = True;
                    return "Connexion a la base de donnée bien établie";
                }
            }

            catch (PDOException $e)
            {
                error_log("Erreur PDO [" . date('Y-m-d H:i:s') . "] : " .$e->getMessage() . " (SQLSTATE: " . $e->getCode() . ")",3,BDD_LOGS_PROJET); // système de suivi d'erreurs
                $this->est_connecter = False;
                return "Une erreur est survenue. Veuillez réessayer plus tard.";
            }
        }


        /* Méthode de déconnexion de la bdd*/
        public function deconnexion()
        {
            //on verifie si la connexion est deja bien etabli 
            if ($this->est_connecter === True)
            {
                $this->pdo = null; // revient a fermer la connexion a la base donnée sous pdo
                $this->est_connecter = False; 
                return "deconnexion a la base donnée effectuer avec succés";
            }
        }

        public function requetes_sql(string $requete_preparer, array $arguments = [])
        {
            if ($this->est_connecter === True)
            {
                try
                {
                    $requete_proteger = $this->pdo->prepare($requete_preparer);
			        $requete_proteger->execute($arguments);
                    return $requete_proteger;
                }
                
                catch(Exception $e)
                {
                    return "erreur lors de l'execution de la requete -> $e";
                }
            }
        }

        /**
        * Récupère TOUTES les lignes
        */
        public function fetchAll(string $requete_preparer, array $arguments = []): array
        {
            $data = $this->requetes_sql($requete_preparer, $arguments);
            return $data->fetchAll();
        }

        /**
        * Récupère une SEULE ligne (pour la connexion)
        */
        public function fetch(string $requete_preparer, array $arguments = [])
        {
            $data = $this->requetes_sql($requete_preparer, $arguments);
            return $data->fetch(); // Retourne un tableau simple ou false
        }
    }
?>