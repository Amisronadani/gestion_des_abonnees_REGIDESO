<?php
/**
 * RESIDESO - Voir un Point de Service
 */
$page_title = 'Détails Point de Service';
$current_page = 'points_service';
require_once __DIR__ . '/../includes/header.php';
require_access('points_service');

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: index.php'); exit; }

$stmt = $pdo->prepare("SELECT p.*, c.reference_Contrat, c.Statut_Contrat, a.nom_abonnes, a.prenom_abonnes 
    FROM point_de_service p 
    LEFT JOIN contrat c ON p.id_Contrat = c.id_Contrat 
    LEFT JOIN abonne a ON c.idabonne_abonnes = a.idabonne_abonnes 
    WHERE p.id_Point_de_service = ?");
$stmt->execute([$id]);
$p = $stmt->fetch();
if (!$p) { set_flash('danger', 'Point de service introuvable.'); header('Location: index.php'); exit; }

// Compteurs liés
$stmt2 = $pdo->prepare("SELECT * FROM compteur WHERE id_Point_de_service = ? ORDER BY id_Compteur DESC");
$stmt2->execute([$id]);
$compteurs = $stmt2->fetchAll();
?>

<div class="row mb-3">
    <div class="col-md-8">
        <h3 style="font-weight: 700; color: #1a1a2e;"><i class="fas fa-map-marker-alt mr-2" style="color: #ff6b6b;"></i>Point de Service : <?= sanitize($p['libelle_Point_de_service']) ?></h3>
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
                <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Informations</h3>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr><th style="width:40%">Libellé</th><td><?= sanitize($p['libelle_Point_de_service']) ?></td></tr>
                    <tr><th>Adresse</th><td><?= sanitize($p['adresse_Point_de_service']) ?></td></tr>
                    <tr><th>Contrat</th><td><a href="<?= BASE_URL ?>/contrats/view.php?id=<?= $p['id_Contrat'] ?>"><?= sanitize($p['reference_Contrat']) ?></a></td></tr>
                    <tr><th>Statut Contrat</th><td><span class="badge <?= $p['Statut_Contrat'] === 'Actif' ? 'badge-actif' : 'badge-expire' ?>"><?= sanitize($p['Statut_Contrat']) ?></span></td></tr>
                    <tr><th>Abonné</th><td><a href="<?= BASE_URL ?>/abonnes/view.php?id=<?= $p['id_Contrat'] ?>"><?= sanitize($p['nom_abonnes'] . ' ' . $p['prenom_abonnes']) ?></a></td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header" style="background: linear-gradient(135deg, #4facfe, #00f2fe); color: white;">
                <h3 class="card-title"><i class="fas fa-tachometer-alt mr-2"></i>Compteurs installés (<?= count($compteurs) ?>)</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead><tr><th>Numéro</th><th>Type</th><th>État</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php if (empty($compteurs)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-3">Aucun compteur</td></tr>
                        <?php else: foreach ($compteurs as $cm): ?>
                        <tr>
                            <td><?= sanitize($cm['numero_Compteur']) ?></td>
                            <td><?= sanitize($cm['Typecompteur_Compteur']) ?></td>
                            <td><span class="badge <?= $cm['Etat_compteur_Compteur'] === 'Actif' ? 'badge-actif' : 'badge-inactif' ?>"><?= sanitize($cm['Etat_compteur_Compteur']) ?></span></td>
                            <td><a href="<?= BASE_URL ?>/compteurs/view.php?id=<?= $cm['id_Compteur'] ?>" class="btn btn-info btn-sm"><i class="fas fa-eye"></i></a></td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
