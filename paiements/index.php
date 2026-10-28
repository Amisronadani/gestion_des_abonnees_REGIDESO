<?php
/**
 * RESIDESO - Liste des Paiements
 */
$page_title = 'Paiements';
$current_page = 'paiements';
require_once __DIR__ . '/../includes/header.php';
require_access('paiements');

$search = trim($_GET['search'] ?? '');
$where = '';
$params = [];

if ($search !== '') {
    $where = "WHERE pay.mode_paiement LIKE ? OR pay.Statut_paiement LIKE ? OR r.periode_releve LIKE ? OR a.nom_abonnes LIKE ? OR a.prenom_abonnes LIKE ?";
    $like = "%$search%";
    $params = [$like, $like, $like, $like, $like];
}

$sql_count = "SELECT COUNT(*) FROM paiement pay 
    LEFT JOIN releve r ON pay.releve_id_releve = r.id_releve 
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

$sql = "SELECT pay.*, r.periode_releve, r.ConsommationCalculee_releve, cm.numero_Compteur, a.nom_abonnes, a.prenom_abonnes 
    FROM paiement pay 
    LEFT JOIN releve r ON pay.releve_id_releve = r.id_releve 
    LEFT JOIN compteur cm ON r.id_Compteur = cm.id_Compteur 
    LEFT JOIN point_de_service p ON cm.id_Point_de_service = p.id_Point_de_service 
    LEFT JOIN contrat c ON p.id_Contrat = c.id_Contrat 
    LEFT JOIN abonne a ON c.idabonne_abonnes = a.idabonne_abonnes 
    $where ORDER BY pay.id_paiement DESC LIMIT $per_page OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$paiements = $stmt->fetchAll();
?>

<div class="row mb-3">
    <div class="col-md-8">
        <h3 style="font-weight: 700; color: #1a1a2e;">
            <i class="fas fa-money-bill-wave mr-2" style="color: #00d2d3;"></i>Gestion des Paiements
        </h3>
    </div>
    <div class="col-md-4 text-right">
        <a href="create.php" class="btn btn-primary"><i class="fas fa-plus mr-1"></i> Nouveau Paiement</a>
    </div>
</div>

<div class="search-bar">
    <form method="GET" class="form-inline">
        <div class="input-group w-100">
            <input type="text" name="search" class="form-control" placeholder="Rechercher par mode, statut, période, abonné..." value="<?= sanitize($search) ?>">
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
                    <th>Date</th>
                    <th>Abonné</th>
                    <th>Période</th>
                    <th>Montant</th>
                    <th>Mode</th>
                    <th>Statut</th>
                    <th style="width:200px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($paiements)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x mb-2 d-block"></i>Aucun paiement trouvé</td></tr>
                <?php else: foreach ($paiements as $pay): ?>
                <tr>
                    <td><strong><?= $pay['id_paiement'] ?></strong></td>
                    <td><?= format_date($pay['date_paiement']) ?></td>
                    <td><?= sanitize($pay['nom_abonnes'] . ' ' . $pay['prenom_abonnes']) ?></td>
                    <td><span class="badge badge-info"><?= sanitize($pay['periode_releve']) ?></span></td>
                    <td><strong><?= format_montant($pay['montant_paiement']) ?></strong></td>
                    <td><?= sanitize($pay['mode_paiement']) ?></td>
                    <td><span class="badge <?= $pay['Statut_paiement'] === 'Payé' ? 'badge-paye' : 'badge-impaye' ?>"><?= sanitize($pay['Statut_paiement']) ?></span></td>
                    <td>
                        <a href="view.php?id=<?= $pay['id_paiement'] ?>" class="btn btn-info btn-sm" title="Voir"><i class="fas fa-eye"></i></a>
                        <a href="facture.php?id=<?= $pay['id_paiement'] ?>" class="btn btn-success btn-sm" title="Facture"><i class="fas fa-file-invoice"></i></a>
                        <a href="edit.php?id=<?= $pay['id_paiement'] ?>" class="btn btn-warning btn-sm" title="Modifier"><i class="fas fa-edit"></i></a>
                        <?php if (has_role('Administrateur')): ?>
                        <button onclick="confirmDelete('delete.php?id=<?= $pay['id_paiement'] ?>')" class="btn btn-danger btn-sm" title="Supprimer"><i class="fas fa-trash"></i></button>
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
