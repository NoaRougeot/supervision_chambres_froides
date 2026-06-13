import time, random
from classes_perso.sql import Bdd

class Modules :

    def __init__(self):

        self.bdd = Bdd()  # Instanciation de Bdd
        self.bdd.bdd_connexion()  # Connexion automatique à la base de données

    def infos_chambres(self):
        
        """Récupère et affiche la liste des chambres."""

        requete = "SELECT id_chambre, nom_chambre, type_chambre, plage_min, plage_max FROM Chambre"
        return self.bdd.fetchAll(requete, [])

    def nouvelles_temperatures(self, nb_donnees: int, interval_min: float, id_chambre: int, type_chambre: bool):

        """Insère des températures simulées."""
        
        if type_chambre == 1:
            x, y = 2, 8

        else:
            x, y = -25, -18

        for _ in range(nb_donnees):

            temperature : int = random.randrange(x, y)
            horodatage = int(time.time())

            requete = "INSERT INTO Temperature (temperature, horodatage_temperature, id_chambre) VALUES (%s, %s, %s)"
            params = (temperature, horodatage, id_chambre)

            self.bdd.requete(requete, params)
            self.bdd.bdd.commit()

            # Attendre entre chaque relevé (sauf le dernier)
            if _ < nb_donnees - 1:
                time.sleep(interval_min * 60)

    def nouvelle_etat_porte(self, id_chambre: int, etat: int):

        """Enregistre manuellement l'état de la porte (1 = ouverte, 0 = fermée)."""

        horodatage = int(time.time())
        
        requete = "INSERT INTO Porte (etat_porte, horodatage_porte, id_chambre) VALUES (%s, %s, %s)"
        params = (etat, horodatage, id_chambre)
        
        self.bdd.requete(requete, params)
        self.bdd.bdd.commit()
        
    def nouvelle_alertes(self, type_alerte: int, id_chambre: int):

        """Déclenche manuellement une alerte."""

        horodatage = int(time.time())
        date_ack = 0  # 0 = non acquitté par défaut
        
        requete = "INSERT INTO Alerte (type_alerte, horodatage_alerte, date_ack_alarme, id_chambre) VALUES (%s, %s, %s, %s)"
        params = (type_alerte, horodatage, date_ack, id_chambre)
        
        self.bdd.requete(requete, params)
        self.bdd.bdd.commit()

    def tout_supprimer(self):

        """
        Vide les tables de données de supervision.
        Utilise TRUNCATE pour réinitialiser les auto-incréments à 1.
        """

        try:
            # Désactivation des contraintes de clés étrangères 
            # (nécessaire car les tables sont liées à la table Chambre)
            self.bdd.requete("SET FOREIGN_KEY_CHECKS = 0", ())
            
            # Vidage des tables
            self.bdd.requete("TRUNCATE TABLE Temperature", ())
            self.bdd.requete("TRUNCATE TABLE Porte", ())
            self.bdd.requete("TRUNCATE TABLE Alerte", ())
            
            # Réactivation des contraintes
            self.bdd.requete("SET FOREIGN_KEY_CHECKS = 1", ())
            
            # Validation finale
            self.bdd.bdd.commit()
            return True
        
        except Exception as e:
            print("Erreur fatale lors du nettoyage de la base : {}".format(e))
            return False