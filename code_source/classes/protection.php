<?php

    require_once __DIR__ . '/../config.php';

    /**
    * Classe pour gérer les protections d'accès et les redirections.
    * Chaque méthode vérifie une condition et redirige si nécessaire.
    */

    class Protection
    {
        // Lien de redirection par défaut en cas d'accès interdit
        private const REDIRECTION_DEFAUT = 'Location: ../interdiction/acces_interdit.php';

        /**
        * Redirige vers un lien donné (par défaut : REDIRECTION_DEFAUT)
        *  $lien L'en-tête HTTP de redirection (ex: "Location: url.php")
        */
        function redirection_avertissement($lien = 'Location: ../interdiction/acces_interdit.php')
        {   
            header($lien);
            exit;
        }

        /**
        * Vérifie si l'utilisateur est connecté. Sinon, redirige.
        * $lien_anexes Lien personnalisé pour la redirection
        */
        function url_protection($lien_anexes = self::REDIRECTION_DEFAUT)
        {
            if (!$_SESSION["logged"])
            {
                this->redirection_avertissement($lien_anexes);
            }
        }

        /**
        * Vérifie si l'utilisateur n'a PAS configuré la 2FA. Si c'est le cas, redirige.
        * $lien_anexes Lien personnalisé pour la redirection
        */
        function not_tfa($lien_anexes = self::REDIRECTION_DEFAUT)
        {
            if (empty($_SESSION['cle_secrete']))
            {
                this->redirection_avertissement($lien_anexes);
            }
        }

        /**
        * Vérifie si l'utilisateur a DÉJÀ configuré et validé la 2FA.
        * Si oui, redirige (pour éviter l'accès au formulaire de setup).
        * $lien_anexes Lien personnalisé pour la redirection
        */
        function already_tfa($lien_anexes = self::REDIRECTION_DEFAUT) // restreindre l'acces au formulaire de setup 2fa quand on a deja une clé secrete en base de données
        {
            if (!empty($_SESSION['cle_secrete']) && $_SESSION['2fa_validated'] === true)
            {
                this->redirection_avertissement($lien_anexes);
            }
        }

        /**
        * Vérifie si l'utilisateur est connecté, a une clé 2FA, mais que la 2FA n'est PAS validée.
        * Si c'est le cas, redirige.
        * $lien_anexes Lien personnalisé pour la redirection
        */
        function tfa_url_protection($lien_anexes = self::REDIRECTION_DEFAUT)
        {
            if (!empty($_SESSION['cle_secrete']) && $_SESSION["logged"] && $_SESSION['2fa_validated'] !== True)
            {
                this->redirection_avertissement($lien_anexes);
            }
        }

        /**
        * Vérifie si l'utilisateur est connecté ET n'a PAS le bon statut (droits).
        * Si c'est le cas, redirige.
        * $user_status Statut requis (ex: "admin", "user")
        * $lien_anexes Lien personnalisé pour la redirection
        */
        function status_protection(string $user_status, $lien_anexes = self::REDIRECTION_DEFAUT)
        {
            if ($_SESSION["logged"] && $_SESSION["droits"] !== $user_status)
            {
                this->redirection_avertissement($lien_anexes);
            }
        }
    }
?>