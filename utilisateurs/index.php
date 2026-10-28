<?php
/**
 * RESIDESO - Liste des Utilisateurs (Admin uniquement)
 */
$page_title = 'Utilisateurs';
$current_page = 'utilisateurs';
require_once __DIR__ . '/../includes/header.php';
require_access('utilisateurs');

$search = trim($_GET['search'] ?? '');
$where = '';
$params = [];

if ($search !== '') {
    $where = "WHERE nom_utilisateur LIKE ? OR prenom_utilisateur LIKE ? OR email_utilisateur LIKE ? OR role_utilisateur LIKE ?";
    $like = "%$search%";
    $params = [$like, $like, $like, $like];
}

$pagination = paginate($pdo, 'utilisateurs', $where, $params);

$sql = "SELECT * FROM utilisateurs $where ORDER BY id_utilisateur DESC LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();
?>

<div class="row mb-3">
    <div class="col-md-8">
        <h3 style="font-weight: 700; color: #1a1a2e;">
            <i class="fas fa-user-cog mr-2" style="color: #e056fd;"></i>Gestion des Utilisateurs
        </h3>
    </div>
    <div class="col-md-4 text-right">
        <a href="create.php" class="btn btn-primary"><i class="fas fa-user-plus mr-1"></i> Nouvel Utilisateur</a>
    </div>
</div>

<div class="search-bar">
    <form method="GET" class="form-inline">
        <div class="input-group w-100">
            <input type="text" name="search" class="form-control" placeholder="Rechercher par nom, email, rôle..." value="<?= sanitize($search) ?>">
            <div class="input-group-append">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search mr-1"></i> Rechercher</button>
                <?php if ($search): ?><a href="index.php" class="btn btn-secondary"><i class="fas fa-times"></i></a><?php endif; ?>
            </div>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Statut</th>
                    <th>Date création</th>
                    <th style="width:160px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x mb-2 d-block"></i>Aucun utilisateur trouvé</td></tr>
                <?php else: foreach ($users as $u): ?>
                <tr>
                    <td><strong><?= $u['id_utilisateur'] ?></strong></td>
                    <td><?= sanitize($u['nom_utilisateur']) ?></td>
                    <td><?= sanitize($u['prenom_utilisateur']) ?></td>
                    <td><?= sanitize($u['email_utilisateur']) ?></td>
                    <td>
                        <?php
                        $roleBadge = [
                            'Administrateur' => 'background: linear-gradient(135deg, #e74c3c, #c0392b)',
                            'Agent' => 'background: linear-gradient(135deg, #f39c12, #e67e22)',
                            'Utilisateur' => 'background: linear-gradient(135deg, #667eea, #764ba2)',
                        ];
                        $style = $roleBadge[$u['role_utilisateur']] ?? 'background: #999';
                        ?>
                        <span class="badge" style="<?= $style ?>; color: white; padding: 5px 12px;"><?= sanitize($u['role_utilisateur']) ?></span>
                    </td>
                    <td><span class="badge <?= $u['statut_utilisateur'] === 'Actif' ? 'badge-actif' : 'badge-inactif' ?>"><?= sanitize($u['statut_utilisateur']) ?></span></td>
                    <td><?= format_date($u['date_creation'], 'd/m/Y H:i') ?></td>
                    <td>
                        <a href="view.php?id=<?= $u['id_utilisateur'] ?>" class="btn btn-info btn-sm"><i class="fas fa-eye"></i></a>
                        <a href="edit.php?id=<?= $u['id_utilisateur'] ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                        <?php if ($u['id_utilisateur'] != $_SESSION['user_id']): ?>
                        <button onclick="confirmDelete('delete.php?id=<?= $u['id_utilisateur'] ?>')" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer"><?php display_pagination($pagination, $search); ?></div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
