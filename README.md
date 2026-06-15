# Système de Supervision de Chambres Froides

<div align="center">

## 🌐 Démo en ligne

[Ouvrir le site de démonstration](https://rougeot.alwaysdata.net/bts_projet_chambre_froides/index.php)

*Accès jury — identifiants fournis séparément.*

---

![BTS CIEL](https://img.shields.io/badge/BTS%20CIEL-Session%202026-blue?style=for-the-badge)
![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Python](https://img.shields.io/badge/Python-3.10-3776AB?style=for-the-badge&logo=python&logoColor=white)
![MariaDB](https://img.shields.io/badge/MariaDB-MySQL-003545?style=for-the-badge&logo=mariadb&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)
![License](https://img.shields.io/badge/License-GPL--3.0-green?style=for-the-badge)

**Projet BTS CIEL — Session 2026**  
*Cybersécurité, Informatique et Réseaux, option A — Informatique et Réseaux*  
**Lycée Polyvalent Pierre Mendès-France, Épinal (88)**

</div>

---

## Présentation du Projet

Ce projet consiste en la **conception et la réalisation d'un système de supervision complet** pour les chambres froides du service de restauration scolaire du **Lycée Polyvalent Pierre Mendès-France d'Épinal**.

Il répond aux exigences strictes de la réglementation alimentaire (**HACCP**, arrêté du 21 décembre 2009) en permettant une **surveillance en temps réel**, fiable et sécurisée des températures et des états de portes.

Le projet s'articule autour de **deux composants principaux** :

- **`code_source/`** — Interface web de supervision (PHP/MariaDB)
- **`code_source_simulateur/`** — Simulateur Python pour injecter des données de test en base

---

## Fonctionnalités

| Fonctionnalité | Description |
|---|---|
| 🌡️ Mesure des températures | Acquisition automatique et horodatée |
| 🚪 État des portes | Détection ouverture/fermeture en temps réel |
| 📊 Visualisation | Graphiques interactifs (Chart.js) + tableaux historiques |
| 🔔 Alertes | Mail, alarme sonore, voyant lumineux |
| 🔐 Authentification 2FA | TOTP compatible Google Authenticator |
| 🛡️ Sécurité | Anti brute-force, TLS 1.3, injections SQL |
| 👤 Gestion des droits | Chef de service / Techniciens |
| ⚙️ Administration | Gestion des utilisateurs et des chambres |
| 🧪 Simulateur BDD | Injection de données de test via CLI Python |

---

## Technologies utilisées

### Interface Web (Noa ROUGEOT)

| Couche | Technologies |
|---|---|
| **Backend** | PHP 8, PDO (requêtes préparées) |
| **Frontend** | HTML5, CSS3, Bootstrap 5, Chart.js |
| **Base de données** | MariaDB / MySQL |
| **Authentification** | 2FA TOTP — bibliothèque `RobThree/TwoFactorAuth` + `endroid/qr-code` |
| **Sécurité** | TLS 1.3, anti brute-force (session), gestion fine des droits |
| **Architecture** | Programmation Orientée Objet (POO) |

### Simulateur (Noa ROUGEOT)

| Couche | Technologies |
|---|---|
| **Langage** | Python 3 |
| **Connecteur BDD** | `mysql-connector-python` |
| **Interface** | CLI interactive (menu ASCII) |

### Acquisition capteurs (Thimoty VANDERNOOT)

- ESP32, MQTT, Raspberry Pi

---

## 📁 Structure du Projet

```
supervision_chambres_froides/
│
├── code_source/                         # Interface web PHP
│   ├── index.php                        # Page d'accueil / Connexion
│   ├── config.php                       # Configuration globale (chemins, constantes)
│   │
│   ├── classes/                         # Classes PHP (POO)
│   │   ├── bdd.php                      # Classe Base_de_donnee (PDO)
│   │   ├── utilisateur.php              # Classe Utilisateur (auth, anti brute-force)
│   │   ├── double_authentification.php  # Classe DoubleAuthentification (TOTP)
│   │   ├── protection.php              # Classe Protection (contrôle d'accès)
│   │   ├── chambre_froides.php         # Classe Chambre_froides
│   │   └── alertes.php                 # Classe Alertes (génération messages Bootstrap)
│   │
│   ├── authentification/               # Flux 2FA (setup TOTP, vérification OTP)
│   ├── espaces_perso/                  # Espace utilisateur connecté
│   ├── temperatures/                   # Graphiques et historiques de températures
│   ├── gestion_utilisateur_chambres/   # Administration : utilisateurs & chambres
│   ├── header/                         # Navbar commune
│   ├── interdiction/                   # Page d'accès refusé
│   └── logs/                           # Journalisation (BDD, brute-force, accès)
│       ├── bdd_erreur.log
│       ├── securite_bruteforce.log
│       └── securite_acces.log
│
├── code_source_simulateur/             # Simulateur Python (injection BDD)
│   ├── simulateur.py                   # Point d'entrée CLI
│   └── classes_perso/
│       ├── modules.py                  # Modules : températures, portes, alertes
│       └── sql.py                      # Classe Bdd (mysql-connector)
│
├── LICENSE                             # GPL-3.0
└── README.md
```

---

## 🧪 Simulateur de données

Le simulateur est un outil **CLI en Python** permettant d'alimenter la base de données avec des données de test sans disposer de capteurs physiques.

### Fonctionnalités du simulateur

- Afficher la liste des chambres froides enregistrées
- Insérer des **températures simulées** (aléatoires, dans la plage réglementaire)
- Insérer un **état de porte** (ouverte / fermée)
- Insérer une **alerte** manuellement
- Tout supprimer (remise à zéro des données de test)

### Lancer le simulateur

```bash
cd code_source_simulateur
python3 simulateur.py
```

> Pensez à configurer vos paramètres de connexion BDD et a decompresser le fichier mysql avant de lancer le simulateur.

---

## Sécurité

- **Authentification 2 facteurs (2FA/TOTP)** via `RobThree/TwoFactorAuth` — compatible Google Authenticator, Authy, etc.
- **Protection anti brute-force** : blocage automatique après 5 tentatives échouées (5 min)
- **Requêtes préparées PDO** : protection native contre les injections SQL
- **Chiffrement TLS 1.3** pour le transport des données
- **Gestion fine des droits** : séparation Chef de service / Techniciens
- **Journalisation** : logs d'accès, d'erreurs BDD et de tentatives de brute-force

---

## Installation (Interface Web)

> Pré-requis : PHP 8+, MariaDB/MySQL, serveur web (Apache/Nginx), Composer

```bash
# 1. Cloner le dépôt
git clone https://github.com/NoaRougeot/supervision_chambres_froides.git
cd supervision_chambres_froides/code_source

# 2. Installer les dépendances PHP
composer install

# 3. Configurer la base de données
# → Importer le schéma SQL (si fourni)
# → Renseigner vos paramètres dans config.php

# 4. Déployer sur votre serveur web
# → Pointer le DocumentRoot vers code_source/
```

---

## 👥 Équipe du Projet

| Membre | Rôle |
|---|---|
| **Noa ROUGEOT** | Interface Web, Authentification 2FA, Backend PHP, Graphiques, Simulateur Python |
| **Thimoty VANDERNOOT** | Acquisition capteurs (ESP32), MQTT, Raspberry Pi |
| **Quentin FIRMANN** | Base de données, Système d'alertes, Envoi de mails |

**Superviseurs académiques :** M. Frédéric BALLAND & M. Éric DERENDINGER  
**Établissement :** Lycée Polyvalent Pierre Mendès-France — Épinal (88)

---

## 📄 Licence

Ce projet est distribué sous licence **GPL-3.0**. Voir le fichier [LICENSE](LICENSE) pour plus de détails.
