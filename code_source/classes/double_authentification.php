<?php
require_once __DIR__ . '/../config.php';
require_once PROTECTION_CLASS_PROJET;
require_once BDD_CLASS_PROJET;
require_once TFA_PROJET;
require_once UTILISATEUR_CLASS_PROJET;

session_start();
$protection = new Protection();
$protection->url_protection();

use RobThree\Auth\TwoFactorAuth;
use RobThree\Auth\Providers\Qr\EndroidQrCodeProvider;

class DoubleAuthentification
{
    private $bdd;
    private $tfa;
    private $utilisateur;
    private $nom_app = 'Chambre Froides';  // Nom qui apparaît dans l'app Authenticator
    private $bdd_requete = "UPDATE Utilisateur SET cle_secrete = :secret WHERE pseudo = :pseudo";

    public function __construct()
    {
        $this->tfa = new TwoFactorAuth(new EndroidQrCodeProvider());
        $this->bdd = new Base_de_donnee();
        $this->utilisateur = new Utilisateur();
        $this->bdd->connexion(); // Établit la connexion une fois pour toutes
    }

    public function initialiser_2fa(): string
    {
        // Si déjà un secret temporaire → on le réutilise (évite de régénérer à chaque refresh)
        if (empty($_SESSION['cle_secrete_tempo'])) 
        {
            $_SESSION['cle_secrete_tempo'] = $this->tfa->createSecret(); // 160 bits recommandé
        }
        
        $cle_temporaire = $_SESSION['cle_secrete_tempo'];
        $label = $_SESSION['pseudo'] . ' - ' . $this->nom_app;

        return $this->tfa->getQRCodeImageAsDataUri($label, $cle_temporaire);
    }

    public function valider_code_2fa(string $code): bool
    {
        $cle_temporaire = $_SESSION['cle_secrete_tempo'] ?? null;

        if (!$cle_temporaire) 
        {
            return false;
        }

        // Vérifie avec tolérance de 1 période (~30s avant/après)
        $validation = $this->tfa->verifyCode($cle_temporaire, $code, 1);

        if ($validation)
        {
            $_SESSION['cle_secrete'] = $cle_temporaire;
            unset($_SESSION['cle_secrete_tempo']); // ← nettoie

            try
            {
                $requete_proteger = $this->bdd->requetes_sql($this->bdd_requete, [':secret' => $cle_temporaire, ':pseudo' => $_SESSION["pseudo"]]);

                // Retourne true seulement si au moins une ligne a été modifiée
                return $requete_proteger->rowCount() > 0;
            }

            catch (PDOException $e) 
            {
                // Log l'erreur pour debug
                error_log("Erreur sauvegarde 2FA : " . $e->getMessage());
                return false;
            }
        }

        return false; // code TOTP invalide
    }

    /**
    * Permet de forcer un nouveau secret temporaire (bouton "Régénérer")
    */
    public function regenerer_2fa(): void
    {
        unset($_SESSION['cle_secrete_tempo']);
    }

    public function verifier_totp(string $code): bool
    {
        $secret = $_SESSION['cle_secrete'] ?? null;

        if (!$secret) 
        {
            return false;
        }

        return $this->tfa->verifyCode($secret, $code, 1);
    }

    function desactiver_2fa(): bool
    {
        $requete_proteger = $this->bdd->requetes_sql($this->bdd_requete, [':secret' => '', ':pseudo' => $_SESSION["pseudo"]]);
        $this->utilisateur->deconnexion_utilisateur(False);
        $_SESSION['2fa_validated'] = False;

        // Retourne true seulement si au moins une ligne a été modifiée
        return $requete_proteger->rowCount() > 0;
    }
}
?>