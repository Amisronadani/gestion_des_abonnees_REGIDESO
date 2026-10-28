<?php
/**
 * RESIDESO - Voir un Relevé
 */
$page_title = 'Détails Relevé';
$current_page = 'releves';
require_once __DIR__ . '/../includes/header.php';
require_access('releves');

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: index.php'); exit; }

$stmt = $pdo->prepare("SELECT r.*, cm.numero_Compteur, cm.Typecompteur_Compteur, p.libelle_Point_de_service, c.reference_Contrat, a.nom_abonnes, a.prenom_abonnes, a.adresse_abonne
    FROM releve r 
    LEFT JOIN compteur cm ON r.id_Compteur = cm.id_Compteur 
    LEFT JOIN point_de_service p ON cm.id_Point_de_service = p.id_Point_de_service 
    LEFT JOIN contrat c ON p.id_Contrat = c.id_Contrat 
    LEFT JOIN abonne a ON c.idabonne_abonnes = a.idabonne_abonnes 
    WHERE r.id_releve = ?");
$stmt->execute([$id]);
$r = $stmt->fetch();
if (!$r) { set_flash('danger', 'Relevé introuvable.'); header('Location: index.php'); exit; }

// Paiements liés
$stmt2 = $pdo->prepare("SELECT * FROM paiement WHERE releve_id_releve = ? ORDER BY id_paiement DESC");
$stmt2->execute([$id]);
$paiements = $stmt2->fetchAll();
?>

<div class="row mb-3">
    <div class="col-md-8">
        <h3 style="font-weight: 700; color: #1a1a2e;"><i class="fas fa-chart-line mr-2" style="color: #1dd1a1;"></i>Relevé #<?= $id ?> — <?= sanitize($r['periode_releve']) ?></h3>
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
                <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Informations du relevé</h3>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr><th style="width:45%">Période</th><td><span class="badge badge-info"><?= sanitize($r['periode_releve']) ?></span></td></tr>
                    <tr><th>Date</th><td><?= format_date($r['date_releve'], 'd/m/Y à H:i') ?></td></tr>
                    <tr><th>Index Précédent</th><td><?= number_format($r['indexPrecedent_releve'], 2, ',', ' ') ?> m³</td></tr>
                    <tr><th>Index Suivant</th><td><?= number_format($r['indexSuivant_releve'], 2, ',', ' ') ?> m³</td></tr>
                    <tr><th>Consommation</th><td><strong style="color: #2e7d32; font-size: 1.1rem;"><?= number_format($r['ConsommationCalculee_releve'], 2, ',', ' ') ?> m³</strong></td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header" style="background: linear-gradient(135deg, #4facfe, #00f2fe); color: white;">
                <h3 class="card-title"><i class="fas fa-link mr-2"></i>Informations liées</h3>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr><th style="width:45%">Compteur</th><td><code><?= sanitize($r['numero_Compteur']) ?></code> (<?= sanitize($r['Typecompteur_Compteur']) ?>)</td></tr>
                    <tr><th>Point de Service</th><td><?= sanitize($r['libelle_Point_de_service']) ?></td></tr>
                    <tr><th>Contrat</th><td><?= sanitize($r['reference_Contrat']) ?></td></tr>
                    <tr><th>Abonné</th><td><?= sanitize($r['nom_abonnes'] . ' ' . $r['prenom_abonnes']) ?></td></tr>
                    <tr><th>Adresse</th><td><?= sanitize($r['adresse_abonne']) ?></td></tr>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header" style="background: linear-gradient(135deg, #11998e, #38ef7d); color: white;">
                <h3 class="card-title"><i class="fas fa-money-bill-wave mr-2"></i>Paiements (<?= count($paiements) ?>)</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead><tr><th>Date</th><th>Montant</th><th>Mode</th><th>Statut</th></tr></thead>
                    <tbody>
                        <?php if (empty($paiements)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-3">Aucun paiement</td></tr>
                        <?php else: foreach ($paiements as $pay): ?>
                        <tr>
                            <td><?= format_date($pay['date_paiement']) ?></td>
                            <td><strong><?= format_montant($pay['montant_paiement']) ?></strong></td>
                            <td><?= sanitize($pay['mode_paiement']) ?></td>
                            <td><span class="badge <?= $pay['Statut_paiement'] === 'Payé' ? 'badge-paye' : 'badge-impaye' ?>"><?= sanitize($pay['Statut_paiement']) ?></span></td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
