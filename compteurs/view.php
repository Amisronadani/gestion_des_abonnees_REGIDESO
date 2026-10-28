<?php
/**
 * RESIDESO - Voir un Compteur
 */
$page_title = 'Détails Compteur';
$current_page = 'compteurs';
require_once __DIR__ . '/../includes/header.php';
require_access('compteurs');

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: index.php'); exit; }

$stmt = $pdo->prepare("SELECT cm.*, p.libelle_Point_de_service, p.adresse_Point_de_service, c.reference_Contrat, a.nom_abonnes, a.prenom_abonnes
    FROM compteur cm 
    LEFT JOIN point_de_service p ON cm.id_Point_de_service = p.id_Point_de_service 
    LEFT JOIN contrat c ON p.id_Contrat = c.id_Contrat 
    LEFT JOIN abonne a ON c.idabonne_abonnes = a.idabonne_abonnes 
    WHERE cm.id_Compteur = ?");
$stmt->execute([$id]);
$cm = $stmt->fetch();
if (!$cm) { set_flash('danger', 'Compteur introuvable.'); header('Location: index.php'); exit; }

// Relevés liés
$stmt2 = $pdo->prepare("SELECT * FROM releve WHERE id_Compteur = ? ORDER BY id_releve DESC LIMIT 10");
$stmt2->execute([$id]);
$releves = $stmt2->fetchAll();
?>

<div class="row mb-3">
    <div class="col-md-8">
        <h3 style="font-weight: 700; color: #1a1a2e;"><i class="fas fa-tachometer-alt mr-2" style="color: #54a0ff;"></i>Compteur : <?= sanitize($cm['numero_Compteur']) ?></h3>
    </div>
    <div class="col-md-4 text-right">
        <a href="edit.php?id=<?= $id ?>" class="btn btn-warning"><i class="fas fa-edit mr-1"></i> Modifier</a>
        <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i> Retour</a>
    </div>
</div>

<div class="row">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header" style="background: linear-gradient(135deg, #667eea, #764ba2); color: white;">
                <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Informations</h3>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr><th style="width:45%">Numéro</th><td><code><?= sanitize($cm['numero_Compteur']) ?></code></td></tr>
                    <tr><th>Type</th><td><?= sanitize($cm['Typecompteur_Compteur']) ?></td></tr>
                    <tr><th>Date de Pose</th><td><?= format_date($cm['DatePose_Compteur']) ?></td></tr>
                    <tr><th>État</th><td><span class="badge <?= $cm['Etat_compteur_Compteur'] === 'Actif' ? 'badge-actif' : 'badge-inactif' ?>"><?= sanitize($cm['Etat_compteur_Compteur']) ?></span></td></tr>
                    <tr><th>Point de Service</th><td><?= sanitize($cm['libelle_Point_de_service']) ?></td></tr>
                    <tr><th>Contrat</th><td><?= sanitize($cm['reference_Contrat']) ?></td></tr>
                    <tr><th>Abonné</th><td><?= sanitize($cm['nom_abonnes'] . ' ' . $cm['prenom_abonnes']) ?></td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card">
            <div class="card-header" style="background: linear-gradient(135deg, #11998e, #38ef7d); color: white;">
                <h3 class="card-title"><i class="fas fa-chart-line mr-2"></i>Derniers relevés (<?= count($releves) ?>)</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead><tr><th>Période</th><th>Date</th><th>Index Préc.</th><th>Index Suiv.</th><th>Conso.</th></tr></thead>
                    <tbody>
                        <?php if (empty($releves)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-3">Aucun relevé</td></tr>
                        <?php else: foreach ($releves as $r): ?>
                        <tr>
                            <td><?= sanitize($r['periode_releve']) ?></td>
                            <td><?= format_date($r['date_releve'], 'd/m/Y H:i') ?></td>
                            <td><?= number_format($r['indexPrecedent_releve'], 2, ',', ' ') ?></td>
                            <td><?= number_format($r['indexSuivant_releve'], 2, ',', ' ') ?></td>
                            <td><strong><?= number_format($r['ConsommationCalculee_releve'], 2, ',', ' ') ?> m³</strong></td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
