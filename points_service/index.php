<?php
/**
 * RESIDESO - Liste des Points de Service
 */
$page_title = 'Points de Service';
$current_page = 'points_service';
require_once __DIR__ . '/../includes/header.php';
require_access('points_service');

$search = trim($_GET['search'] ?? '');
$where = '';
$params = [];

if ($search !== '') {
    $where = "WHERE p.libelle_Point_de_service LIKE ? OR p.adresse_Point_de_service LIKE ? OR c.reference_Contrat LIKE ? OR a.nom_abonnes LIKE ?";
    $like = "%$search%";
    $params = [$like, $like, $like, $like];
}

$sql_count = "SELECT COUNT(*) FROM point_de_service p LEFT JOIN contrat c ON p.id_Contrat = c.id_Contrat LEFT JOIN abonne a ON c.idabonne_abonnes = a.idabonne_abonnes $where";
$stmt = $pdo->prepare($sql_count);
$stmt->execute($params);
$total = $stmt->fetchColumn();

$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 10;
$total_pages = max(1, ceil($total / $per_page));
$page = min($page, $total_pages);
$offset = ($page - 1) * $per_page;
$pagination = ['page' => $page, 'per_page' => $per_page, 'total' => $total, 'total_pages' => $total_pages, 'offset' => $offset];

$sql = "SELECT p.*, c.reference_Contrat, a.nom_abonnes, a.prenom_abonnes 
        FROM point_de_service p 
        LEFT JOIN contrat c ON p.id_Contrat = c.id_Contrat 
        LEFT JOIN abonne a ON c.idabonne_abonnes = a.idabonne_abonnes 
        $where ORDER BY p.id_Point_de_service DESC LIMIT $per_page OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$points = $stmt->fetchAll();
?>

<div class="row mb-3">
    <div class="col-md-8">
        <h3 style="font-weight: 700; color: #1a1a2e;">
            <i class="fas fa-map-marker-alt mr-2" style="color: #ff6b6b;"></i>Gestion des Points de Service
        </h3>
    </div>
    <div class="col-md-4 text-right">
        <a href="create.php" class="btn btn-primary"><i class="fas fa-plus mr-1"></i> Nouveau Point</a>
    </div>
</div>

<div class="search-bar">
    <form method="GET" class="form-inline">
        <div class="input-group w-100">
            <input type="text" name="search" class="form-control" placeholder="Rechercher par libellé, adresse, contrat, abonné..." value="<?= sanitize($search) ?>">
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
                    <th>Libellé</th>
                    <th>Adresse</th>
                    <th>Contrat</th>
                    <th>Abonné</th>
                    <th style="width:160px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($points)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x mb-2 d-block"></i>Aucun point de service trouvé</td></tr>
                <?php else: foreach ($points as $p): ?>
                <tr>
                    <td><strong><?= $p['id_Point_de_service'] ?></strong></td>
                    <td><?= sanitize($p['libelle_Point_de_service']) ?></td>
                    <td><?= sanitize($p['adresse_Point_de_service']) ?></td>
                    <td><a href="<?= BASE_URL ?>/contrats/view.php?id=<?= $p['id_Contrat'] ?>"><?= sanitize($p['reference_Contrat']) ?></a></td>
                    <td><?= sanitize($p['nom_abonnes'] . ' ' . $p['prenom_abonnes']) ?></td>
                    <td>
                        <a href="view.php?id=<?= $p['id_Point_de_service'] ?>" class="btn btn-info btn-sm" title="Voir"><i class="fas fa-eye"></i></a>
                        <a href="edit.php?id=<?= $p['id_Point_de_service'] ?>" class="btn btn-warning btn-sm" title="Modifier"><i class="fas fa-edit"></i></a>
                        <?php if (has_role('Administrateur')): ?>
                        <button onclick="confirmDelete('delete.php?id=<?= $p['id_Point_de_service'] ?>')" class="btn btn-danger btn-sm" title="Supprimer"><i class="fas fa-trash"></i></button>
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
