<?php
/**
 * RESIDESO - Voir un Contrat
 */
$page_title = 'Détails Contrat';
$current_page = 'contrats';
require_once __DIR__ . '/../includes/header.php';
require_access('contrats');

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: index.php'); exit; }

$stmt = $pdo->prepare("SELECT c.*, a.nom_abonnes, a.prenom_abonnes, a.telephone_abonnes FROM contrat c LEFT JOIN abonne a ON c.idabonne_abonnes = a.idabonne_abonnes WHERE c.id_Contrat = ?");
$stmt->execute([$id]);
$c = $stmt->fetch();
if (!$c) { set_flash('danger', 'Contrat introuvable.'); header('Location: index.php'); exit; }

// Points de service liés
$stmt2 = $pdo->prepare("SELECT * FROM point_de_service WHERE id_Contrat = ?");
$stmt2->execute([$id]);
$points = $stmt2->fetchAll();
?>

<div class="row mb-3">
    <div class="col-md-8">
        <h3 style="font-weight: 700; color: #1a1a2e;"><i class="fas fa-file-contract mr-2" style="color: #feca57;"></i>Contrat : <?= sanitize($c['reference_Contrat']) ?></h3>
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
                <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Informations du contrat</h3>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr><th style="width:40%">Référence</th><td><?= sanitize($c['reference_Contrat']) ?></td></tr>
                    <tr><th>Abonné</th><td><a href="<?= BASE_URL ?>/abonnes/view.php?id=<?= $c['idabonne_abonnes'] ?>"><?= sanitize($c['nom_abonnes'] . ' ' . $c['prenom_abonnes']) ?></a></td></tr>
                    <tr><th>Date Début</th><td><?= format_date($c['dateDebut_Contrat']) ?></td></tr>
                    <tr><th>Date Fin</th><td><?= format_date($c['dateFin_Contrat']) ?></td></tr>
                    <tr><th>Statut</th><td><span class="badge <?= $c['Statut_Contrat'] === 'Actif' ? 'badge-actif' : 'badge-expire' ?>"><?= sanitize($c['Statut_Contrat']) ?></span></td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header" style="background: linear-gradient(135deg, #ff6b6b, #ee5a24); color: white;">
                <h3 class="card-title"><i class="fas fa-map-marker-alt mr-2"></i>Points de service (<?= count($points) ?>)</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead><tr><th>Libellé</th><th>Adresse</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php if (empty($points)): ?>
                            <tr><td colspan="3" class="text-center text-muted py-3">Aucun point de service</td></tr>
                        <?php else: foreach ($points as $p): ?>
                        <tr>
                            <td><?= sanitize($p['libelle_Point_de_service']) ?></td>
                            <td><?= sanitize($p['adresse_Point_de_service']) ?></td>
                            <td><a href="<?= BASE_URL ?>/points_service/view.php?id=<?= $p['id_Point_de_service'] ?>" class="btn btn-info btn-sm"><i class="fas fa-eye"></i></a></td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
