# Système de Supervision de Chambres Froides

**Projet BTS CIEL - Session 2026**  
*Cybersécurité, Informatique et Réseaux, option A Informatique et Réseaux*

---

## Présentation du Projet

Ce projet consiste en la **conception et la réalisation d’un système de supervision complet** pour les chambres froides du service de restauration scolaire du **Lycée Polyvalent Pierre Mendès-France d’Épinal**.

Il répond aux exigences strictes de la réglementation alimentaire (HACCP, arrêté du 21 décembre 2009) en permettant une **surveillance en temps réel**, fiable et sécurisée des températures et des états de portes.

---

## Fonctionnalités

### Fonctionnalités principales
- Mesure automatique de la température des chambres froides
- Détection de l’état des portes (ouverte/fermée)
- Enregistrement horodaté des données
- Visualisation interactive via une interface web (graphiques + tableaux)
- Système d’alertes (mail, alarme sonore, voyant lumineux)
- Authentification sécurisée **2 Facteurs (TOTP)**
- Gestion des utilisateurs et des chambres (espace administrateur)

---

## Technologies utilisées (Partie Noa ROUGEOT)

- **Backend** : PHP 8 + PDO (requêtes préparées)
- **Frontend** : HTML5, CSS3, Bootstrap 5, Chart.js
- **Base de données** : MariaDB / MySQL
- **Sécurité** : 
  - Authentification 2FA avec TOTP (Google Authenticator)
  - Chiffrement TLS 1.3
  - Protection contre les injections SQL
  - Gestion fine des droits (Chef de service / Techniciens)
- **Architecture** : Programmation Orientée Objet (POO)

---

## 📁 Structure du Projet

```bash
bts_projet_chambres_froides/
├── index.php                    # Page d'accueil
├── config.php                   # Configuration globale
├── classes/                     # Classes PHP (POO)
│   ├── alertes.php
│   ├── bdd.php
│   ├── utilisateur.php
│   ├── double_authentication.php
│   ├── protection.php
│   └── chambre_froides.php
├── authentification/            # Gestion 2FA
├── espaces_perso/               # Espace utilisateur
├── temperatures/                # Graphiques et historiques
├── gestion_utilisateur_chambres/# Ajout utilisateurs & chambres
├── interdiction/                # Page d'accès refusé
└── logs/                        # Journalisation
```

---

## 👥 Équipe du Projet

- **Noa ROUGEOT** — Interface Web, Authentification 2FA, Backend PHP & Graphiques
  
- **Thimoty VANDERNOOT** — Acquisition capteurs (ESP32), MQTT, Raspberry Pi

- **Quentin FIRMANN** — Base de données, Système d’alertes & Envoi de mails

Superviseurs académiques : **M. Fréderic BALLAND** & M. **Éric DERENDINGER**
