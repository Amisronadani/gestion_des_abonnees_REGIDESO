<?php
/**
 * RESIDESO - Voir un Paiement
 */
$page_title = 'Détails Paiement';
$current_page = 'paiements';
require_once __DIR__ . '/../includes/header.php';
require_access('paiements');

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: index.php'); exit; }

$stmt = $pdo->prepare("SELECT pay.*, r.periode_releve, r.date_releve, r.indexPrecedent_releve, r.indexSuivant_releve, r.ConsommationCalculee_releve,
    cm.numero_Compteur, cm.Typecompteur_Compteur, p.libelle_Point_de_service, c.reference_Contrat, 
    a.nom_abonnes, a.prenom_abonnes, a.telephone_abonnes, a.adresse_abonne
    FROM paiement pay 
    LEFT JOIN releve r ON pay.releve_id_releve = r.id_releve 
    LEFT JOIN compteur cm ON r.id_Compteur = cm.id_Compteur 
    LEFT JOIN point_de_service p ON cm.id_Point_de_service = p.id_Point_de_service 
    LEFT JOIN contrat c ON p.id_Contrat = c.id_Contrat 
    LEFT JOIN abonne a ON c.idabonne_abonnes = a.idabonne_abonnes 
    WHERE pay.id_paiement = ?");
$stmt->execute([$id]);
$pay = $stmt->fetch();
if (!$pay) { set_flash('danger', 'Paiement introuvable.'); header('Location: index.php'); exit; }
?>

<div class="row mb-3">
    <div class="col-md-7">
        <h3 style="font-weight: 700; color: #1a1a2e;"><i class="fas fa-money-bill-wave mr-2" style="color: #00d2d3;"></i>Paiement #<?= $id ?></h3>
    </div>
    <div class="col-md-5 text-right">
        <a href="facture.php?id=<?= $id ?>" class="btn btn-success"><i class="fas fa-file-invoice mr-1"></i> Facture</a>
        <a href="edit.php?id=<?= $id ?>" class="btn btn-warning"><i class="fas fa-edit mr-1"></i> Modifier</a>
        <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i> Retour</a>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header" style="background: linear-gradient(135deg, #667eea, #764ba2); color: white;">
                <h3 class="card-title"><i class="fas fa-credit-card mr-2"></i>Détails du paiement</h3>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr><th style="width:45%">Date</th><td><?= format_date($pay['date_paiement']) ?></td></tr>
                    <tr><th>Montant</th><td><strong style="font-size: 1.3rem; color: #2e7d32;"><?= format_montant($pay['montant_paiement']) ?></strong></td></tr>
                    <tr><th>Mode</th><td><?= sanitize($pay['mode_paiement']) ?></td></tr>
                    <tr><th>Statut</th><td><span class="badge <?= $pay['Statut_paiement'] === 'Payé' ? 'badge-paye' : 'badge-impaye' ?>"><?= sanitize($pay['Statut_paiement']) ?></span></td></tr>
                </table>
            </div>
        </div>
        <div class="card">
            <div class="card-header" style="background: linear-gradient(135deg, #11998e, #38ef7d); color: white;">
                <h3 class="card-title"><i class="fas fa-chart-line mr-2"></i>Relevé associé</h3>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr><th style="width:45%">Période</th><td><span class="badge badge-info"><?= sanitize($pay['periode_releve']) ?></span></td></tr>
                    <tr><th>Index Précédent</th><td><?= number_format($pay['indexPrecedent_releve'], 2, ',', ' ') ?> m³</td></tr>
                    <tr><th>Index Suivant</th><td><?= number_format($pay['indexSuivant_releve'], 2, ',', ' ') ?> m³</td></tr>
                    <tr><th>Consommation</th><td><strong><?= number_format($pay['ConsommationCalculee_releve'], 2, ',', ' ') ?> m³</strong></td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header" style="background: linear-gradient(135deg, #4facfe, #00f2fe); color: white;">
                <h3 class="card-title"><i class="fas fa-user mr-2"></i>Informations abonné</h3>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr><th style="width:45%">Abonné</th><td><?= sanitize($pay['nom_abonnes'] . ' ' . $pay['prenom_abonnes']) ?></td></tr>
                    <tr><th>Téléphone</th><td><?= sanitize($pay['telephone_abonnes']) ?></td></tr>
                    <tr><th>Adresse</th><td><?= sanitize($pay['adresse_abonne']) ?></td></tr>
                    <tr><th>Contrat</th><td><?= sanitize($pay['reference_Contrat']) ?></td></tr>
                    <tr><th>Point de Service</th><td><?= sanitize($pay['libelle_Point_de_service']) ?></td></tr>
                    <tr><th>Compteur</th><td><code><?= sanitize($pay['numero_Compteur']) ?></code></td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
