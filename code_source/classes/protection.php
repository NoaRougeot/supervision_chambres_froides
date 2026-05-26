<?php

require_once __DIR__ . '/../config.php';

class Protection
{
    function url_protection()
    {
        if (!$_SESSION["logged"])
        {
            header('Location: ../interdiction/acces_interdit.php');
            exit;
        }
    }

    function not_tfa()
    {
        if (empty($_SESSION['cle_secrete']))
        {
            header("Location: ../interdiction/acces_interdit.php");
            exit;
        }
    }

    function already_tfa()
    {
        if (!empty($_SESSION['cle_secrete']) && $_SESSION['2fa_validated'] === true)
        {
            header("Location: ../interdiction/acces_interdit.php");
            exit;
        }
    }

    function tfa_url_protection()
    {
        if (!empty($_SESSION['cle_secrete']) && $_SESSION["logged"] && $_SESSION['2fa_validated'] !== True)
        {
            header('Location: ../interdiction/acces_interdit.php');
            exit;
        }
    }

    function status_protection(string $user_status)
    {
        if ($_SESSION["logged"] && $_SESSION["droits"] !== $user_status)
        {
            header('Location: ../interdiction/acces_interdit.php');
            exit;
        }
    }
}
?>