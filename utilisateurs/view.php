<?php
/**
 * RESIDESO - Voir un Utilisateur
 */
$page_title = 'Détails Utilisateur';
$current_page = 'utilisateurs';
require_once __DIR__ . '/../includes/header.php';
require_access('utilisateurs');

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: index.php'); exit; }

$stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id_utilisateur = ?");
$stmt->execute([$id]);
$u = $stmt->fetch();
if (!$u) { set_flash('danger', 'Utilisateur introuvable.'); header('Location: index.php'); exit; }

$roleBadge = [
    'Administrateur' => 'background: linear-gradient(135deg, #e74c3c, #c0392b)',
    'Agent' => 'background: linear-gradient(135deg, #f39c12, #e67e22)',
    'Utilisateur' => 'background: linear-gradient(135deg, #667eea, #764ba2)',
];
$style = $roleBadge[$u['role_utilisateur']] ?? 'background: #999';

// Permissions du rôle
$perms = [
    'Utilisateur' => ['Abonnés', 'Contrats', 'Points de Service', 'Compteurs'],
    'Agent' => ['Relevés', 'Paiements', 'Points de Service'],
    'Administrateur' => ['Abonnés', 'Contrats', 'Points de Service', 'Compteurs', 'Relevés', 'Paiements', 'Utilisateurs'],
];
$all_modules = ['Abonnés', 'Contrats', 'Points de Service', 'Compteurs', 'Relevés', 'Paiements', 'Utilisateurs'];
$user_perms = $perms[$u['role_utilisateur']] ?? [];
?>

<div class="row mb-3">
    <div class="col-md-8">
        <h3 style="font-weight: 700; color: #1a1a2e;"><i class="fas fa-user-cog mr-2" style="color: #e056fd;"></i>Utilisateur : <?= sanitize($u['prenom_utilisateur'] . ' ' . $u['nom_utilisateur']) ?></h3>
    </div>
    <div class="col-md-4 text-right">
        <a href="edit.php?id=<?= $id ?>" class="btn btn-warning"><i class="fas fa-edit mr-1"></i> Modifier</a>
        <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i> Retour</a>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header" style="background: linear-gradient(135deg, #667eea, #764ba2); color: white;">
                <h3 class="card-title"><i class="fas fa-id-card mr-2"></i>Informations</h3>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr><th style="width:40%">Nom</th><td><?= sanitize($u['nom_utilisateur']) ?></td></tr>
                    <tr><th>Prénom</th><td><?= sanitize($u['prenom_utilisateur']) ?></td></tr>
                    <tr><th>Email</th><td><?= sanitize($u['email_utilisateur']) ?></td></tr>
                    <tr><th>Rôle</th><td><span class="badge" style="<?= $style ?>; color: white; padding: 6px 14px;"><?= sanitize($u['role_utilisateur']) ?></span></td></tr>
                    <tr><th>Statut</th><td><span class="badge <?= $u['statut_utilisateur'] === 'Actif' ? 'badge-actif' : 'badge-inactif' ?>"><?= sanitize($u['statut_utilisateur']) ?></span></td></tr>
                    <tr><th>Date création</th><td><?= format_date($u['date_creation'], 'd/m/Y à H:i') ?></td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header" style="background: linear-gradient(135deg, #11998e, #38ef7d); color: white;">
                <h3 class="card-title"><i class="fas fa-shield-alt mr-2"></i>Droits d'accès</h3>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead><tr><th>Module</th><th>Accès</th></tr></thead>
                    <tbody>
                        <?php foreach ($all_modules as $mod): ?>
                        <tr>
                            <td><?= $mod ?></td>
                            <td>
                                <?php if (in_array($mod, $user_perms)): ?>
                                    <span class="badge badge-actif"><i class="fas fa-check mr-1"></i> Autorisé (CRUD)</span>
                                <?php else: ?>
                                    <span class="badge badge-inactif"><i class="fas fa-times mr-1"></i> Non autorisé</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
