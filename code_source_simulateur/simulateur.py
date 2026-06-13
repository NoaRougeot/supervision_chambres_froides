from classes_perso.modules import Modules

BANDEAU = """
╔══════════════════════════════════════════════════════════════╗
║       Simulateur BDD — Supervision chambres froides          ║
║       BTS CIEL — Noa ROUGEOT — Session 2026                  ║
╚══════════════════════════════════════════════════════════════╝
"""
 
MENU = """
  ┌─────────────────────────────────────────────────┐
  │  MENU PRINCIPAL                                 │
  ├─────────────────────────────────────────────────┤
  │  [1]  Info chambres                             │ 
  │  [2]  Insérer températures                      │                    
  │  [3]  Insérer un état de porte                  │
  │  [4]  Insérer une alerte                        │
  │  [5]  Tout suprimer                             │
  │  [6]  Quitter                                   │
  └─────────────────────────────────────────────────┘
"""

def main():
    mod = Modules()
    
    while True:
        print(BANDEAU)
        print(MENU)
        
        choix = input("Votre choix : ").strip()
        
        if choix == "1":

            chambres = mod.infos_chambres()

            print("\n--- Liste des chambres ---")

            for c in chambres:
                type_str = "Positive" if c[2] == 1 else "Négative"
                print("ID: {} | Nom: {} | Type: {}".format(c[0], c[1], type_str))

            input("\nAppuyer sur Entrée pour revenir au menu...")

        elif choix == "2":

            id_chambre = int(input("ID de la chambre : "))
            type_chambre = int(input("Type de chambre (1 = positive, 0 = négative) : "))
            nb = int(input("Nombre de données à générer : "))
            interval = float(input("Intervalle en minutes : "))
            
            print("Génération en cours...")
            mod.nouvelles_temperatures(nb, interval, id_chambre, type_chambre)
            print("Terminé !")

        elif choix == "3":

            id_chambre = int(input("ID de la chambre : "))
            etat = int(input("État de la porte (1 = ouverte, 0 = fermée) : "))
            mod.nouvelle_etat_porte(id_chambre, etat)
            print("État de porte enregistré !")

        elif choix == "4":

            id_chambre = int(input("ID de la chambre : "))
            type_alerte = int(input("Type d'alerte (0: seuil min, 1: seuil max, 2: porte ouverte) : "))
            mod.nouvelle_alertes(type_alerte, id_chambre)
            print("Alerte déclenchée !")

        elif choix == "5":

            confirm = input("Êtes-vous sûr de vouloir supprimer TOUTES les données ? (o/n) : ")

            if confirm.lower() == 'o':

                print("Nettoyage en cours...")

                if mod.tout_supprimer():
                    print("Base de données réinitialisée avec succès !")

                else:
                    print("Une erreur est survenue lors de la suppression.")

            input("Appuyer sur Entrée pour revenir au menu...")

        elif choix == "6":

            print("Au revoir !")
            mod.bdd.bdd_deconnexion()
            break

if __name__ == "__main__":
    main()
