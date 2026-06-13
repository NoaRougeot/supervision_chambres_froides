import mysql.connector
from config_bdd import conf

class Bdd:

    def __init__(self):
        self.bdd = None
        self.cursor = None
        self.etat_conexion = False

    def bdd_connexion(self):

        if self.etat_conexion:
            return "Base de donnee deja connectee"

        try:
            self.bdd = mysql.connector.connect(**conf.liste_params_connexion)
            self.cursor = self.bdd.cursor()
            self.etat_conexion = True

            return "Connexion reussie"

        except Exception as e:
            return "Connexion impossible -> {}".format(e)

    def bdd_deconnexion(self):

        if not self.etat_conexion:
            return "Aucune connexion active"

        try:
            self.cursor.close()
            self.bdd.close()

            self.etat_conexion = False

            return "Deconnexion reussie"

        except Exception as e:
            return "Erreur -> {}".format(e)

    def requete(self, requete: str, params):

        if not self.etat_conexion:
            return None

        try:
            self.cursor.execute(requete, params)
            return self.cursor

        except Exception as e:
            return "Erreur -> {}".format(e)

    def fetchAll(self, requete: str, params):

        cursor = self.requete(requete, params)

        if cursor:
            return cursor.fetchall()

        return []

    def fetchOne(self, requete: str, params):

        cursor = self.requete(requete, params)

        if cursor:
            return cursor.fetchone()

        return None