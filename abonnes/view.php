<?php
/**
 * RESIDESO - Voir un Abonné
 */
$page_title = 'Détails Abonné';
$current_page = 'abonnes';
require_once __DIR__ . '/../includes/header.php';
require_access('abonnes');

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: index.php'); exit; }

$stmt = $pdo->prepare("SELECT * FROM abonne WHERE idabonne_abonnes = ?");
$stmt->execute([$id]);
$a = $stmt->fetch();

if (!$a) {
    set_flash('danger', 'Abonné introuvable.');
    header('Location: index.php');
    exit;
}

// Contrats liés
$stmt2 = $pdo->prepare("SELECT * FROM contrat WHERE idabonne_abonnes = ? ORDER BY id_Contrat DESC");
$stmt2->execute([$id]);
$contrats = $stmt2->fetchAll();
?>

<div class="row mb-3">
    <div class="col-md-8">
        <h3 style="font-weight: 700; color: #1a1a2e;">
            <i class="fas fa-user mr-2" style="color: #48dbfb;"></i>Détails de l'Abonné #<?= $id ?>
        </h3>
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
                <h3 class="card-title"><i class="fas fa-id-card mr-2"></i>Informations personnelles</h3>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr><th style="width:40%">Nom</th><td><?= sanitize($a['nom_abonnes']) ?></td></tr>
                    <tr><th>Prénom</th><td><?= sanitize($a['prenom_abonnes']) ?></td></tr>
                    <tr><th>Type</th><td><span class="badge badge-info"><?= sanitize($a['type_abonne_abonnes']) ?></span></td></tr>
                    <tr><th>Téléphone</th><td><?= sanitize($a['telephone_abonnes']) ?></td></tr>
                    <tr><th>Email</th><td><?= sanitize($a['email_abonnes']) ?></td></tr>
                    <tr><th>Adresse</th><td><?= sanitize($a['adresse_abonne']) ?></td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header" style="background: linear-gradient(135deg, #11998e, #38ef7d); color: white;">
                <h3 class="card-title"><i class="fas fa-file-contract mr-2"></i>Contrats associés (<?= count($contrats) ?>)</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead><tr><th>Référence</th><th>Statut</th><th>Début</th><th>Fin</th></tr></thead>
                    <tbody>
                        <?php if (empty($contrats)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-3">Aucun contrat</td></tr>
                        <?php else: ?>
                            <?php foreach ($contrats as $c): ?>
                            <tr>
                                <td><a href="<?= BASE_URL ?>/contrats/view.php?id=<?= $c['id_Contrat'] ?>"><?= sanitize($c['reference_Contrat']) ?></a></td>
                                <td><span class="badge <?= $c['Statut_Contrat'] === 'Actif' ? 'badge-actif' : 'badge-expire' ?>"><?= sanitize($c['Statut_Contrat']) ?></span></td>
                                <td><?= format_date($c['dateDebut_Contrat']) ?></td>
                                <td><?= format_date($c['dateFin_Contrat']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
