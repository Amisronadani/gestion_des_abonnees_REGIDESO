<?php
/**
 * RESIDESO - Liste des Abonnés (avec recherche et pagination)
 */
$page_title = 'Abonnés';
$current_page = 'abonnes';
require_once __DIR__ . '/../includes/header.php';
require_access('abonnes');

// Recherche
$search = trim($_GET['search'] ?? '');
$where = '';
$params = [];

if ($search !== '') {
    $where = "WHERE nom_abonnes LIKE ? OR prenom_abonnes LIKE ? OR telephone_abonnes LIKE ? OR email_abonnes LIKE ? OR type_abonne_abonnes LIKE ?";
    $like = "%$search%";
    $params = [$like, $like, $like, $like, $like];
}

// Pagination
$pagination = paginate($pdo, 'abonne', $where, $params);

// Requête
$sql = "SELECT * FROM abonne $where ORDER BY idabonne_abonnes DESC LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$abonnes = $stmt->fetchAll();
?>

<!-- Titre + Bouton Ajouter -->
<div class="row mb-3">
    <div class="col-md-8">
        <h3 style="font-weight: 700; color: #1a1a2e;">
            <i class="fas fa-users mr-2" style="color: #48dbfb;"></i>Gestion des Abonnés
        </h3>
    </div>
    <div class="col-md-4 text-right">
        <a href="create.php" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i> Nouvel Abonné
        </a>
    </div>
</div>

<!-- Barre de recherche -->
<div class="search-bar">
    <form method="GET" class="form-inline">
        <div class="input-group w-100">
            <input type="text" name="search" class="form-control" placeholder="Rechercher par nom, prénom, téléphone, email..." 
                   value="<?= sanitize($search) ?>">
            <div class="input-group-append">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search mr-1"></i> Rechercher</button>
                <?php if ($search): ?>
                    <a href="index.php" class="btn btn-secondary"><i class="fas fa-times"></i></a>
                <?php endif; ?>
            </div>
        </div>
    </form>
</div>

<!-- Tableau -->
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Type</th>
                    <th>Téléphone</th>
                    <th>Email</th>
                    <th>Adresse</th>
                    <th style="width: 160px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($abonnes)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>Aucun abonné trouvé
                    </td></tr>
                <?php else: ?>
                    <?php foreach ($abonnes as $a): ?>
                    <tr>
                        <td><strong><?= $a['idabonne_abonnes'] ?></strong></td>
                        <td><?= sanitize($a['nom_abonnes']) ?></td>
                        <td><?= sanitize($a['prenom_abonnes']) ?></td>
                        <td><span class="badge badge-info"><?= sanitize($a['type_abonne_abonnes']) ?></span></td>
                        <td><?= sanitize($a['telephone_abonnes']) ?></td>
                        <td><?= sanitize($a['email_abonnes']) ?></td>
                        <td><?= sanitize($a['adresse_abonne']) ?></td>
                        <td>
                            <a href="view.php?id=<?= $a['idabonne_abonnes'] ?>" class="btn btn-info btn-sm" title="Voir">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="edit.php?id=<?= $a['idabonne_abonnes'] ?>" class="btn btn-warning btn-sm" title="Modifier">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php if (has_role('Administrateur')): ?>
                            <button onclick="confirmDelete('delete.php?id=<?= $a['idabonne_abonnes'] ?>')" class="btn btn-danger btn-sm" title="Supprimer">
                                <i class="fas fa-trash"></i>
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        <?php display_pagination($pagination, $search); ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
