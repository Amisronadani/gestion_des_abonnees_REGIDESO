<?php
/**
 * RESIDESO - Liste des Relevés
 */
$page_title = 'Relevés';
$current_page = 'releves';
require_once __DIR__ . '/../includes/header.php';
require_access('releves');

$search = trim($_GET['search'] ?? '');
$where = '';
$params = [];

if ($search !== '') {
    $where = "WHERE r.periode_releve LIKE ? OR cm.numero_Compteur LIKE ? OR a.nom_abonnes LIKE ? OR a.prenom_abonnes LIKE ?";
    $like = "%$search%";
    $params = [$like, $like, $like, $like];
}

$sql_count = "SELECT COUNT(*) FROM releve r 
    LEFT JOIN compteur cm ON r.id_Compteur = cm.id_Compteur 
    LEFT JOIN point_de_service p ON cm.id_Point_de_service = p.id_Point_de_service 
    LEFT JOIN contrat c ON p.id_Contrat = c.id_Contrat 
    LEFT JOIN abonne a ON c.idabonne_abonnes = a.idabonne_abonnes $where";
$stmt = $pdo->prepare($sql_count);
$stmt->execute($params);
$total = $stmt->fetchColumn();

$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 10;
$total_pages = max(1, ceil($total / $per_page));
$page = min($page, $total_pages);
$offset = ($page - 1) * $per_page;
$pagination = ['page' => $page, 'per_page' => $per_page, 'total' => $total, 'total_pages' => $total_pages, 'offset' => $offset];

$sql = "SELECT r.*, cm.numero_Compteur, a.nom_abonnes, a.prenom_abonnes 
    FROM releve r 
    LEFT JOIN compteur cm ON r.id_Compteur = cm.id_Compteur 
    LEFT JOIN point_de_service p ON cm.id_Point_de_service = p.id_Point_de_service 
    LEFT JOIN contrat c ON p.id_Contrat = c.id_Contrat 
    LEFT JOIN abonne a ON c.idabonne_abonnes = a.idabonne_abonnes 
    $where ORDER BY r.id_releve DESC LIMIT $per_page OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$releves = $stmt->fetchAll();
?>

<div class="row mb-3">
    <div class="col-md-8">
        <h3 style="font-weight: 700; color: #1a1a2e;">
            <i class="fas fa-chart-line mr-2" style="color: #1dd1a1;"></i>Gestion des Relevés
        </h3>
    </div>
    <div class="col-md-4 text-right">
        <a href="create.php" class="btn btn-primary"><i class="fas fa-plus mr-1"></i> Nouveau Relevé</a>
    </div>
</div>

<div class="search-bar">
    <form method="GET" class="form-inline">
        <div class="input-group w-100">
            <input type="text" name="search" class="form-control" placeholder="Rechercher par période, compteur, abonné..." value="<?= sanitize($search) ?>">
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
                    <th>Période</th>
                    <th>Date</th>
                    <th>Compteur</th>
                    <th>Abonné</th>
                    <th>Index Préc.</th>
                    <th>Index Suiv.</th>
                    <th>Consommation</th>
                    <th style="width:160px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($releves)): ?>
                    <tr><td colspan="9" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x mb-2 d-block"></i>Aucun relevé trouvé</td></tr>
                <?php else: foreach ($releves as $r): ?>
                <tr>
                    <td><strong><?= $r['id_releve'] ?></strong></td>
                    <td><span class="badge badge-info"><?= sanitize($r['periode_releve']) ?></span></td>
                    <td><?= format_date($r['date_releve'], 'd/m/Y') ?></td>
                    <td><code><?= sanitize($r['numero_Compteur']) ?></code></td>
                    <td><?= sanitize($r['nom_abonnes'] . ' ' . $r['prenom_abonnes']) ?></td>
                    <td><?= number_format($r['indexPrecedent_releve'], 2, ',', ' ') ?></td>
                    <td><?= number_format($r['indexSuivant_releve'], 2, ',', ' ') ?></td>
                    <td><strong><?= number_format($r['ConsommationCalculee_releve'], 2, ',', ' ') ?> m³</strong></td>
                    <td>
                        <a href="view.php?id=<?= $r['id_releve'] ?>" class="btn btn-info btn-sm"><i class="fas fa-eye"></i></a>
                        <a href="edit.php?id=<?= $r['id_releve'] ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                        <?php if (has_role('Administrateur')): ?>
                        <button onclick="confirmDelete('delete.php?id=<?= $r['id_releve'] ?>')" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
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
