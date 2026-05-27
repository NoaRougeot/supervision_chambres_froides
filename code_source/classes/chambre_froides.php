<?php
    require_once __DIR__ . '/../config.php';
    require_once BDD_CLASS_PROJET;

    class Chambre_froides
    {
        private $bdd;

        public function __construct()
        {
            $this->bdd = new Base_de_donnee();
            $this->bdd->connexion(); // Établit la connexion une fois pour toutes
        }

        /**
        * Retourne la liste de toutes les chambres froides par ordre alphabétique
        */
        function info_chambres(): array
        {
            return $this->bdd->fetchAll("SELECT * FROM Chambre ORDER BY nom_chambre ASC");
        }

        function info_temps(int $id_chambre, int $start, int $end): array
        {
            // --- Températures (toutes pour le graphique) ---
            $requete = "SELECT horodatage_temperature, temperature 
                        FROM `Temperature` 
                        WHERE id_chambre = :id_chambre 
                        AND horodatage_temperature >= :start 
                        AND horodatage_temperature <= :end 
                        ORDER BY horodatage_temperature ASC";

            $arguments = [
                ':id_chambre' => $id_chambre,
                ':start'      => $start,
                ':end'        => $end ?? $now
            ];

            return $this->bdd->fetchAll($requete, $arguments);
        }

        function info_portes(int $id_chambre, int $start, int $end): array
        {
            // --- États portes (filtrées par chambre et par période) ---
            $requete = "SELECT id_enregistrement_porte, etat_porte, horodatage_porte 
                               FROM `Porte` 
                               WHERE id_chambre = :id_chambre 
                               AND horodatage_porte >= :start 
                               AND horodatage_porte <= :end 
                               ORDER BY horodatage_porte DESC";

            $arguments = [
                ':id_chambre' => $id_chambre,
                ':start'      => $start,
                ':end'        => $end ?? $now
            ];

            return $this->bdd->fetchAll($requete, $arguments);
        }

        function info_alertes(int $id_chambre, int $start, int $end): array
        {
            // --- Alerte (filtrées par chambre et par période) ---
            $requete_alertes = "SELECT id_alerte, type_alerte, horodatage_alerte, date_ack_alarme
                                FROM `Alerte`
                                WHERE id_chambre = :id_chambre
                                AND horodatage_alerte >= :start
                                AND horodatage_alerte <= :end
                                ORDER BY horodatage_alerte DESC";

            $arguments = [
                ':id_chambre' => $id_chambre,
                ':start'      => $start,
                ':end'        => $end
            ];

            return $this->bdd->fetchAll($requete_alertes, $arguments);
        }

        public function ajouter_chambre(): string
        {
            if ($this->bdd->est_connecter !== True) 
            {
                return "Connexion impossible à la base de données";
            }

            $nom       = $_POST['nom_chambre']  ?? '';
            $type      = $_POST['type_chambre'] ?? '';
            $plage_min = $_POST['plage_min']    ?? '';
            $plage_max = $_POST['plage_max']    ?? '';

            if (empty($nom) || $type === '' || $plage_min === '' || $plage_max === '') 
            {
                return "Tous les champs sont obligatoires";
            }

            // Vérification cohérence des températures
            if ((int)$plage_min >= (int)$plage_max) 
            {
                return "La température minimale doit être inférieure à la maximale";
            }

            // Vérification doublon nom
            $check = $this->bdd->requetes_sql("SELECT id_chambre FROM Chambre WHERE nom_chambre = :nom", [':nom' => $nom]);

            if ($check->fetch()) 
            {
                return "Une chambre avec ce nom existe déjà";
            }

            $this->bdd->requetes_sql("INSERT INTO Chambre (nom_chambre, type_chambre, plage_min, plage_max) VALUES (:nom, :type, :plage_min, :plage_max)", [':nom' => $nom, ':type' => (int)$type, ':plage_min' => (int)$plage_min, ':plage_max' => (int)$plage_max,]);
            return "Chambre '$nom' ajoutée avec succès";
        }

        public function modifier_chambre(): string
        {
            $id  = (int)($_POST['id_chambre'] ?? 0);
            $nom = trim($_POST['nom_chambre'] ?? '');
            $type = $_POST['type_chambre'] ?? '';
            $min = $_POST['plage_min'] ?? '';
            $max = $_POST['plage_max'] ?? '';

            if (!$id || $nom === '' || $type === '' || $min === '' || $max === '') 
            {
                return "Tous les champs sont obligatoires.";
            } 
        
            elseif ((int)$min >= (int)$max) 
            {
                return "La température minimale doit être inférieure à la maximale.";
            } 
        
            else 
            {
                $chk = $this->bdd->requetes_sql("SELECT id_chambre FROM Chambre WHERE nom_chambre = :nom AND id_chambre != :id", [':nom' => $nom, ':id' => $id]);

                if ($chk->fetch()) 
                {
                    return "Ce nom de chambre est déjà utilisé.";
                } 
            
                else 
                {
                    $this->bdd->requetes_sql("UPDATE Chambre SET nom_chambre=:nom, type_chambre=:type, plage_min=:min, plage_max=:max WHERE id_chambre=:id", [':nom'=>$nom,':type'=>(int)$type,':min'=>(int)$min,':max'=>(int)$max,':id'=>$id]);
                    return "Chambre '$nom' modifiée avec succès.";
                }
            }
        }

        public function supprimer_chambre(): string
        {
            $id = (int)($_POST['id_chambre'] ?? 0);

            if (!$id) 
            {
                return "Identifiant invalide.";
            } 
        
            else 
            {
                $this->bdd->requetes_sql("DELETE FROM Chambre WHERE id_chambre = :id", [':id' => $id]);
                return "Chambre supprimée.";
            }
        }
    }
?>