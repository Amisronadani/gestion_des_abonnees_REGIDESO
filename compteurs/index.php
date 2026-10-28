<?php
/**
 * RESIDESO - Liste des Compteurs
 */
$page_title = 'Compteurs';
$current_page = 'compteurs';
require_once __DIR__ . '/../includes/header.php';
require_access('compteurs');

$search = trim($_GET['search'] ?? '');
$where = '';
$params = [];

if ($search !== '') {
    $where = "WHERE cm.numero_Compteur LIKE ? OR cm.Typecompteur_Compteur LIKE ? OR cm.Etat_compteur_Compteur LIKE ? OR p.libelle_Point_de_service LIKE ?";
    $like = "%$search%";
    $params = [$like, $like, $like, $like];
}

$sql_count = "SELECT COUNT(*) FROM compteur cm LEFT JOIN point_de_service p ON cm.id_Point_de_service = p.id_Point_de_service $where";
$stmt = $pdo->prepare($sql_count);
$stmt->execute($params);
$total = $stmt->fetchColumn();

$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 10;
$total_pages = max(1, ceil($total / $per_page));
$page = min($page, $total_pages);
$offset = ($page - 1) * $per_page;
$pagination = ['page' => $page, 'per_page' => $per_page, 'total' => $total, 'total_pages' => $total_pages, 'offset' => $offset];

$sql = "SELECT cm.*, p.libelle_Point_de_service, p.adresse_Point_de_service 
        FROM compteur cm 
        LEFT JOIN point_de_service p ON cm.id_Point_de_service = p.id_Point_de_service 
        $where ORDER BY cm.id_Compteur DESC LIMIT $per_page OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$compteurs = $stmt->fetchAll();
?>

<div class="row mb-3">
    <div class="col-md-8">
        <h3 style="font-weight: 700; color: #1a1a2e;">
            <i class="fas fa-tachometer-alt mr-2" style="color: #54a0ff;"></i>Gestion des Compteurs
        </h3>
    </div>
    <div class="col-md-4 text-right">
        <a href="create.php" class="btn btn-primary"><i class="fas fa-plus mr-1"></i> Nouveau Compteur</a>
    </div>
</div>

<div class="search-bar">
    <form method="GET" class="form-inline">
        <div class="input-group w-100">
            <input type="text" name="search" class="form-control" placeholder="Rechercher par numéro, type, état, point de service..." value="<?= sanitize($search) ?>">
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
                    <th>Numéro</th>
                    <th>Type</th>
                    <th>Date de Pose</th>
                    <th>État</th>
                    <th>Point de Service</th>
                    <th style="width:160px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($compteurs)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x mb-2 d-block"></i>Aucun compteur trouvé</td></tr>
                <?php else: foreach ($compteurs as $cm): ?>
                <tr>
                    <td><strong><?= $cm['id_Compteur'] ?></strong></td>
                    <td><code><?= sanitize($cm['numero_Compteur']) ?></code></td>
                    <td><?= sanitize($cm['Typecompteur_Compteur']) ?></td>
                    <td><?= format_date($cm['DatePose_Compteur']) ?></td>
                    <td><span class="badge <?= $cm['Etat_compteur_Compteur'] === 'Actif' ? 'badge-actif' : 'badge-inactif' ?>"><?= sanitize($cm['Etat_compteur_Compteur']) ?></span></td>
                    <td><?= sanitize($cm['libelle_Point_de_service']) ?></td>
                    <td>
                        <a href="view.php?id=<?= $cm['id_Compteur'] ?>" class="btn btn-info btn-sm"><i class="fas fa-eye"></i></a>
                        <a href="edit.php?id=<?= $cm['id_Compteur'] ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                        <?php if (has_role('Administrateur')): ?>
                        <button onclick="confirmDelete('delete.php?id=<?= $cm['id_Compteur'] ?>')" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
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
