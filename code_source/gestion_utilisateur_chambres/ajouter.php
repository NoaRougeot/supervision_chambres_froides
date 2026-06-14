<?php
require_once __DIR__ . '/../config.php';
require_once PROTECTION_CLASS_PROJET;
require_once UTILISATEUR_CLASS_PROJET;
require_once CHAMBRE_FROIDES_CLASS_PROJET;
require_once BDD_CLASS_PROJET;

session_start();
$protection = new Protection();
$protection->url_protection();
$protection->tfa_url_protection();
$protection->status_protection("admin");

$titre_nav = 'Administration';
$icone_nav = 'fas fa-cog';
require_once NAVBAR;

$message_user    = '';
$message_chambre = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    // Ajouter utilisateur 
    if (isset($_POST['pseudo']) && !isset($_POST['action']))
    {
        $ajout_utilisateur = new Utilisateur();
        $message_user = $ajout_utilisateur->ajouter_utilisateur();
    }

    // Modifier utilisateur 
    if (isset($_POST['action']) && $_POST['action'] === 'modifier_utilisateur')
    {
        $modification_utilisateur = new Utilisateur();
        $message_user = $modification_utilisateur->modifier_utilisateur();
    }

    // Supprimer utilisateur
    if (isset($_POST['action']) && $_POST['action'] === 'supprimer_utilisateur')
    {
       $modification_utilisateur = new Utilisateur();
       $message_user = $modification_utilisateur->supprimer_utilisateur();
    }

    // Ajouter chambre
    if (isset($_POST['nom_chambre']) && !isset($_POST['action']))
    {
        $ajout_chambre = new Chambre_froides();
        $message_chambre = $ajout_chambre->ajouter_chambre();
    }

    // Modifier chambre
    if (isset($_POST['action']) && $_POST['action'] === 'modifier_chambre')
    {
        $modification_chambre = new Chambre_froides();
        $message_chambre = $modification_chambre->modifier_chambre();
    }

    // Supprimer chambre
    if (isset($_POST['action']) && $_POST['action'] === 'supprimer_chambre')
    {
        $supression_chambre = new Chambre_froides();
        $message_chambre = $supression_chambre->supprimer_chambre();
    }
}

// Chargement des listes utilisqteurs et chambres froides
$obj_chambres   = new Chambre_froides();
$liste_chambres = $obj_chambres->info_chambres();

$bdd_list = new Base_de_donnee();
$bdd_list->connexion();
$liste_users = $bdd_list->fetchAll("SELECT id_utilisateur, pseudo, nom, prenom, email, droits FROM Utilisateur ORDER BY nom ASC");

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Administration — Supervision chambres froides</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        /* Layout menu-tableau */
        .menu-cell {
            width: 215px;
            vertical-align: top;
            background: #f8f9fa;
        }
        .nav-pills .nav-link {
            text-align: left;
            border-radius: 0;
            font-weight: 500;
        }
        .nav-pills .nav-link.active {
            background-color: #0d6efd;
        }
        .table-wrapper {
            box-shadow: 0 2px 10px rgba(0,0,0,.1);
            border-radius: .5rem;
            overflow: hidden;
        }

        /* Bulle d'aide  */
        .help-bubble {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-left: 5px;
            cursor: help;
            width: 17px; height: 17px;
            border-radius: 50%;
            background: #0d6efd;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            vertical-align: middle;
            transition: transform .15s;
        }
        .help-bubble:hover { transform: scale(1.15); }
        .help-text {
            visibility: hidden;
            opacity: 0;
            position: absolute;
            left: 50%; bottom: 130%;
            transform: translateX(-50%);
            background: #212529;
            color: #fff;
            padding: 9px 12px;
            border-radius: 8px;
            width: 230px;
            font-size: 12px;
            z-index: 999;
            transition: opacity .18s;
            text-align: left;
            pointer-events: none;
        }
        .help-text::after {
            content: "";
            position: absolute;
            top: 100%; left: 50%;
            transform: translateX(-50%);
            border: 6px solid transparent;
            border-top-color: #212529;
        }
        .help-bubble:hover .help-text { visibility: visible; opacity: 1; }
    </style>
</head>

<body class="bg-light">

<!-- NAVBAR injectée via require NAVBAR -->

<!-- TITRE -->
<div class="container">
    <div class="text-center mt-5 mb-4">
        <h1 class="fw-bold">Système de supervision</h1>
        <h3 class="text-muted fw-bold">Administration — Gestion des données</h3>
    </div>

    <!-- TABLEAU PRINCIPAL -->
    <div class="table-wrapper mb-5">
        <table class="table table-bordered align-middle bg-white mb-0">
            <tbody><tr>

                <!-- MENU GAUCHE -->
                <td class="menu-cell p-0">
                    <div class="nav flex-column nav-pills p-2 gap-1">
                        <button class="nav-link active py-3" data-bs-toggle="pill" data-bs-target="#tabUtilisateurs">
                            <i class="fas fa-users me-2"></i>Utilisateurs
                        </button>
                        <button class="nav-link py-3" data-bs-toggle="pill" data-bs-target="#tabChambres">
                            <i class="fas fa-snowflake me-2"></i>Chambres froides
                        </button>
                        <hr class="my-1">
                        <a href="../espaces_perso/espace_personnel.php" class="btn btn-outline-secondary btn-sm mx-1">
                            <i class="fas fa-arrow-left me-1"></i>Retour
                        </a>
                    </div>
                </td>

                <!-- CONTENU -->
                <td class="p-0">
                <div class="tab-content">

                    <!-- ONGLET UTILISATEURS -->
                    <div class="tab-pane fade show active" id="tabUtilisateurs">

                        <?php if ($message_user): ?>
                            <div class="alert <?= str_contains($message_user, 'succès') || str_contains($message_user, 'supprimé') ? 'alert-success' : 'alert-danger' ?> m-3">
                                <?= htmlspecialchars($message_user) ?>
                            </div>
                        <?php endif; ?>

                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Pseudo</th>
                                    <th>Nom Prénom</th>
                                    <th>Email</th>
                                    <th>Droits</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($liste_users as $u): ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars($u['pseudo']) ?></td>
                                <td><?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?></td>
                                <td class="text-muted small"><?= htmlspecialchars($u['email']) ?></td>
                                <td>
                                    <?php if ($u['droits'] === 'admin'): ?>
                                        <span class="badge bg-primary">Technicien</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Chef de service</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <button class="btn btn-warning btn-sm"
                                            data-bs-toggle="modal" data-bs-target="#modalEditUser"
                                            data-id="<?= $u['id_utilisateur'] ?>"
                                            data-pseudo="<?= htmlspecialchars($u['pseudo'], ENT_QUOTES) ?>"
                                            data-nom="<?= htmlspecialchars($u['nom'], ENT_QUOTES) ?>"
                                            data-prenom="<?= htmlspecialchars($u['prenom'], ENT_QUOTES) ?>"
                                            data-email="<?= htmlspecialchars($u['email'], ENT_QUOTES) ?>"
                                            data-droits="<?= htmlspecialchars($u['droits'], ENT_QUOTES) ?>">
                                            <i class="fas fa-user-pen"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm"
                                            data-bs-toggle="modal" data-bs-target="#modalDelUser"
                                            data-id="<?= $u['id_utilisateur'] ?>"
                                            data-pseudo="<?= htmlspecialchars($u['pseudo'], ENT_QUOTES) ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <tr>
                                <td colspan="5" class="text-center p-3">
                                    <button class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#modalAddUser">
                                        <i class="fas fa-plus-circle me-2"></i>Ajouter un utilisateur
                                    </button>
                                </td>
                            </tr>
                            </tbody>
                        </table>

                    </div>

                    <!-- ══ ONGLET CHAMBRES ══ -->
                    <div class="tab-pane fade" id="tabChambres">

                        <?php if ($message_chambre): ?>
                            <div class="alert <?= str_contains($message_chambre, 'succès') || str_contains($message_chambre, 'supprimée') ? 'alert-success' : 'alert-danger' ?> m-3">
                                <?= htmlspecialchars($message_chambre) ?>
                            </div>
                        <?php endif; ?>

                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nom</th>
                                    <th>Type</th>
                                    <th>Plage (°C)</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($liste_chambres as $c): ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars($c['nom_chambre']) ?></td>
                                <td>
                                    <?php if ($c['type_chambre'] == 1): ?>
                                        <span class="badge bg-success">Positive</span>
                                    <?php else: ?>
                                        <span class="badge bg-info text-dark">Négative</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="text-primary fw-bold"><?= htmlspecialchars($c['plage_min']) ?>°</span>
                                    <span class="text-muted mx-1">/</span>
                                    <span class="text-danger fw-bold"><?= htmlspecialchars($c['plage_max']) ?>°</span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <button class="btn btn-warning btn-sm"
                                            data-bs-toggle="modal" data-bs-target="#modalEditChambre"
                                            data-id="<?= $c['id_chambre'] ?>"
                                            data-nom="<?= htmlspecialchars($c['nom_chambre'], ENT_QUOTES) ?>"
                                            data-type="<?= $c['type_chambre'] ?>"
                                            data-min="<?= $c['plage_min'] ?>"
                                            data-max="<?= $c['plage_max'] ?>">
                                            <i class="fas fa-pen-to-square"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm"
                                            data-bs-toggle="modal" data-bs-target="#modalDelChambre"
                                            data-id="<?= $c['id_chambre'] ?>"
                                            data-nom="<?= htmlspecialchars($c['nom_chambre'], ENT_QUOTES) ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <tr>
                                <td colspan="4" class="text-center p-3">
                                    <button class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#modalAddChambre">
                                        <i class="fas fa-plus-circle me-2"></i>Ajouter une chambre froide
                                    </button>
                                </td>
                            </tr>
                            </tbody>
                        </table>

                    </div>

                </div>
                </td>

            </tr></tbody>
        </table>
    </div>
</div>

<p class="text-center text-secondary mb-4">Lycée Pierre Mendès France — Épinal</p>


<!-- MODALS UTILISATEURS -->

<!-- AJOUTER utilisateur -->
<div class="modal fade" id="modalAddUser" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Ajouter un utilisateur</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form method="POST">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Pseudo <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="pseudo" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Email <span class="text-danger">*</span></label>
              <input type="email" class="form-control" name="email" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Nom <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="nom" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Prénom <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="prenom" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Mot de passe <span class="text-danger">*</span></label>
              <div class="input-group">
                <input type="password" class="form-control" name="mdp" id="mdpAdd" required>
                <button type="button" class="btn btn-outline-secondary btn-toggle-mdp" data-target="mdpAdd">
                  <i class="fas fa-eye"></i>
                </button>
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Droits</label>
              <select class="form-select" name="droits">
                <option value="">Chef de service</option>
                <option value="admin">Technicien</option>
              </select>
            </div>
          </div>
          <div class="d-grid mt-4">
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-plus me-2"></i>Ajouter l'utilisateur
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- MODIFIER utilisateur -->
<div class="modal fade" id="modalEditUser" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title"><i class="fas fa-user-pen me-2"></i>Modifier l'utilisateur</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form method="POST">
          <input type="hidden" name="action" value="modifier_utilisateur">
          <input type="hidden" name="id_utilisateur" id="edit-user-id">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Pseudo <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="pseudo" id="edit-user-pseudo" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Email <span class="text-danger">*</span></label>
              <input type="email" class="form-control" name="email" id="edit-user-email" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Nom <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="nom" id="edit-user-nom" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Prénom <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="prenom" id="edit-user-prenom" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">
                Nouveau mot de passe
                <span class="help-bubble">?
                  <span class="help-text">Laisser vide pour conserver le mot de passe actuel.</span>
                </span>
              </label>
              <div class="input-group">
                <input type="password" class="form-control" name="mdp" id="mdpEdit" placeholder="Inchangé si vide">
                <button type="button" class="btn btn-outline-secondary btn-toggle-mdp" data-target="mdpEdit">
                  <i class="fas fa-eye"></i>
                </button>
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Droits</label>
              <select class="form-select" name="droits" id="edit-user-droits">
                <option value="">Chef de service</option>
                <option value="admin">Technicien</option>
              </select>
            </div>
          </div>
          <div class="d-grid mt-4">
            <button type="submit" class="btn btn-warning">
              <i class="fas fa-save me-2"></i>Enregistrer les modifications
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- SUPPRIMER utilisateur -->
<div class="modal fade" id="modalDelUser" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title"><i class="fas fa-trash me-2"></i>Supprimer l'utilisateur</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Voulez-vous vraiment supprimer <strong id="del-user-pseudo"></strong> ?</p>
        <p class="text-danger small"><i class="fas fa-exclamation-triangle me-1"></i>Cette action est irréversible.</p>
        <form method="POST">
          <input type="hidden" name="action" value="supprimer_utilisateur">
          <input type="hidden" name="id_utilisateur" id="del-user-id">
          <div class="d-grid">
            <button type="submit" class="btn btn-danger">
              <i class="fas fa-trash me-2"></i>Supprimer définitivement
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>


<!-- MODALS CHAMBRES FROIDES -->

<!-- AJOUTER chambre -->
<div class="modal fade" id="modalAddChambre" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="fas fa-snowflake me-2"></i>Ajouter une chambre froide</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form method="POST">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label">Nom de la chambre <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="nom_chambre" placeholder="Chambre 3" maxlength="100" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Type</label>
              <select class="form-select" name="type_chambre">
                <option value="1">Positive</option>
                <option value="0">Négative</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Température min (°C) <span class="text-danger">*</span></label>
              <input type="number" class="form-control" name="plage_min" placeholder="-20" min="-128" max="127" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Température max (°C) <span class="text-danger">*</span></label>
              <input type="number" class="form-control" name="plage_max" placeholder="-5" min="-128" max="127" required>
            </div>
          </div>
          <div class="d-grid mt-4">
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-plus me-2"></i>Ajouter la chambre
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- MODIFIER chambre -->
<div class="modal fade" id="modalEditChambre" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title"><i class="fas fa-pen-to-square me-2"></i>Modifier la chambre</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form method="POST">
          <input type="hidden" name="action" value="modifier_chambre">
          <input type="hidden" name="id_chambre" id="edit-chambre-id">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label">Nom de la chambre <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="nom_chambre" id="edit-chambre-nom" maxlength="100" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Type</label>
              <select class="form-select" name="type_chambre" id="edit-chambre-type">
                <option value="1">Positive</option>
                <option value="0">Négative</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Température min (°C) <span class="text-danger">*</span></label>
              <input type="number" class="form-control" name="plage_min" id="edit-chambre-min" min="-128" max="127" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Température max (°C) <span class="text-danger">*</span></label>
              <input type="number" class="form-control" name="plage_max" id="edit-chambre-max" min="-128" max="127" required>
            </div>
          </div>
          <div class="d-grid mt-4">
            <button type="submit" class="btn btn-warning">
              <i class="fas fa-save me-2"></i>Enregistrer les modifications
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- SUPPRIMER chambre -->
<div class="modal fade" id="modalDelChambre" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title"><i class="fas fa-trash me-2"></i>Supprimer la chambre</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Voulez-vous vraiment supprimer <strong id="del-chambre-nom"></strong> ?</p>
        <p class="text-danger small"><i class="fas fa-exclamation-triangle me-1"></i>Toutes les données associées seront perdues.</p>
        <form method="POST">
          <input type="hidden" name="action" value="supprimer_chambre">
          <input type="hidden" name="id_chambre" id="del-chambre-id">
          <div class="d-grid">
            <button type="submit" class="btn btn-danger">
              <i class="fas fa-trash me-2"></i>Supprimer définitivement
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>


<!--SCRIPTS-->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>

// Afficher/masquer mot de passe 
document.querySelectorAll('.btn-toggle-mdp').forEach(btn => {
    btn.addEventListener('click', function () {
        const input = document.getElementById(this.dataset.target);
        const icon  = this.querySelector('i');
        input.type  = input.type === 'password' ? 'text' : 'password';
        icon.className = input.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
    });
});

// Auto-remplissage modal modifier utilisateur
document.getElementById('modalEditUser').addEventListener('show.bs.modal', function (e) {
    const b = e.relatedTarget;
    document.getElementById('edit-user-id').value     = b.dataset.id;
    document.getElementById('edit-user-pseudo').value = b.dataset.pseudo;
    document.getElementById('edit-user-nom').value    = b.dataset.nom;
    document.getElementById('edit-user-prenom').value = b.dataset.prenom;
    document.getElementById('edit-user-email').value  = b.dataset.email;
    document.getElementById('edit-user-droits').value = b.dataset.droits;
    document.getElementById('mdpEdit').value          = '';
});

// Auto-remplissage modal supprimer utilisateur
document.getElementById('modalDelUser').addEventListener('show.bs.modal', function (e) {
    const b = e.relatedTarget;
    document.getElementById('del-user-id').value              = b.dataset.id;
    document.getElementById('del-user-pseudo').textContent    = b.dataset.pseudo;
});

// Auto-remplissage modal modifier chambre
document.getElementById('modalEditChambre').addEventListener('show.bs.modal', function (e) {
    const b = e.relatedTarget;
    document.getElementById('edit-chambre-id').value   = b.dataset.id;
    document.getElementById('edit-chambre-nom').value  = b.dataset.nom;
    document.getElementById('edit-chambre-type').value = b.dataset.type;
    document.getElementById('edit-chambre-min').value  = b.dataset.min;
    document.getElementById('edit-chambre-max').value  = b.dataset.max;
});

// Auto-remplissage modal supprimer chambre
document.getElementById('modalDelChambre').addEventListener('show.bs.modal', function (e) {
    const b = e.relatedTarget;
    document.getElementById('del-chambre-id').value           = b.dataset.id;
    document.getElementById('del-chambre-nom').textContent    = b.dataset.nom;
});

</script>
</body>
</html>