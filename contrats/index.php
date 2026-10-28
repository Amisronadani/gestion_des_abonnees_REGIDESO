<?php
/**
 * RESIDESO - Liste des Contrats
 */
$page_title = 'Contrats';
$current_page = 'contrats';
require_once __DIR__ . '/../includes/header.php';
require_access('contrats');

$search = trim($_GET['search'] ?? '');
$where = '';
$params = [];

if ($search !== '') {
    $where = "WHERE c.reference_Contrat LIKE ? OR c.Statut_Contrat LIKE ? OR a.nom_abonnes LIKE ? OR a.prenom_abonnes LIKE ?";
    $like = "%$search%";
    $params = [$like, $like, $like, $like];
}

// Count pour pagination
$sql_count = "SELECT COUNT(*) FROM contrat c LEFT JOIN abonne a ON c.idabonne_abonnes = a.idabonne_abonnes $where";
$stmt = $pdo->prepare($sql_count);
$stmt->execute($params);
$total = $stmt->fetchColumn();

$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 10;
$total_pages = max(1, ceil($total / $per_page));
$page = min($page, $total_pages);
$offset = ($page - 1) * $per_page;
$pagination = ['page' => $page, 'per_page' => $per_page, 'total' => $total, 'total_pages' => $total_pages, 'offset' => $offset];

$sql = "SELECT c.*, a.nom_abonnes, a.prenom_abonnes FROM contrat c LEFT JOIN abonne a ON c.idabonne_abonnes = a.idabonne_abonnes $where ORDER BY c.id_Contrat DESC LIMIT $per_page OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$contrats = $stmt->fetchAll();
?>

<div class="row mb-3">
    <div class="col-md-8">
        <h3 style="font-weight: 700; color: #1a1a2e;">
            <i class="fas fa-file-contract mr-2" style="color: #feca57;"></i>Gestion des Contrats
        </h3>
    </div>
    <div class="col-md-4 text-right">
        <a href="create.php" class="btn btn-primary"><i class="fas fa-plus mr-1"></i> Nouveau Contrat</a>
    </div>
</div>

<div class="search-bar">
    <form method="GET" class="form-inline">
        <div class="input-group w-100">
            <input type="text" name="search" class="form-control" placeholder="Rechercher par référence, statut, nom abonné..." value="<?= sanitize($search) ?>">
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
                    <th>Référence</th>
                    <th>Abonné</th>
                    <th>Date Début</th>
                    <th>Date Fin</th>
                    <th>Statut</th>
                    <th style="width:160px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($contrats)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x mb-2 d-block"></i>Aucun contrat trouvé</td></tr>
                <?php else: foreach ($contrats as $c): ?>
                <tr>
                    <td><strong><?= $c['id_Contrat'] ?></strong></td>
                    <td><?= sanitize($c['reference_Contrat']) ?></td>
                    <td><?= sanitize($c['nom_abonnes'] . ' ' . $c['prenom_abonnes']) ?></td>
                    <td><?= format_date($c['dateDebut_Contrat']) ?></td>
                    <td><?= format_date($c['dateFin_Contrat']) ?></td>
                    <td><span class="badge <?= $c['Statut_Contrat'] === 'Actif' ? 'badge-actif' : ($c['Statut_Contrat'] === 'Expiré' ? 'badge-expire' : 'badge-inactif') ?>"><?= sanitize($c['Statut_Contrat']) ?></span></td>
                    <td>
                        <a href="view.php?id=<?= $c['id_Contrat'] ?>" class="btn btn-info btn-sm" title="Voir"><i class="fas fa-eye"></i></a>
                        <a href="edit.php?id=<?= $c['id_Contrat'] ?>" class="btn btn-warning btn-sm" title="Modifier"><i class="fas fa-edit"></i></a>
                        <?php if (has_role('Administrateur')): ?>
                        <button onclick="confirmDelete('delete.php?id=<?= $c['id_Contrat'] ?>')" class="btn btn-danger btn-sm" title="Supprimer"><i class="fas fa-trash"></i></button>
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
