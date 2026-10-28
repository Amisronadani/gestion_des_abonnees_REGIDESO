<?php
$page_title = 'Modifier Paiement';
$current_page = 'paiements';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_access('paiements');

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: index.php'); exit; }
$stmt = $pdo->prepare("SELECT * FROM paiement WHERE id_paiement = ?");
$stmt->execute([$id]);
$paiement = $stmt->fetch();
if (!$paiement) { set_flash('danger', 'Paiement introuvable.'); header('Location: index.php'); exit; }

$releves = $pdo->query("SELECT r.id_releve, r.periode_releve, r.ConsommationCalculee_releve, cm.numero_Compteur, a.nom_abonnes, a.prenom_abonnes
    FROM releve r LEFT JOIN compteur cm ON r.id_Compteur = cm.id_Compteur 
    LEFT JOIN point_de_service p ON cm.id_Point_de_service = p.id_Point_de_service 
    LEFT JOIN contrat c ON p.id_Contrat = c.id_Contrat LEFT JOIN abonne a ON c.idabonne_abonnes = a.idabonne_abonnes ORDER BY r.id_releve DESC")->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) $errors[] = 'Token CSRF invalide.';
    $date_paiement = trim($_POST['date_paiement'] ?? '');
    $montant       = (float)($_POST['montant'] ?? 0);
    $mode          = trim($_POST['mode'] ?? '');
    $statut        = trim($_POST['statut'] ?? '');
    $releve_id     = (int)($_POST['releve_id'] ?? 0);
    if (empty($date_paiement)) $errors[] = 'Date obligatoire.';
    if ($montant <= 0)         $errors[] = 'Montant doit être > 0.';
    if ($releve_id <= 0)       $errors[] = 'Sélectionnez un relevé.';
    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE paiement SET date_paiement=?, montant_paiement=?, mode_paiement=?, Statut_paiement=?, releve_id_releve=? WHERE id_paiement=?");
        $stmt->execute([$date_paiement, $montant, $mode, $statut, $releve_id, $id]);
        set_flash('success', 'Paiement modifié avec succès.');
        header('Location: index.php'); exit;
    }
} else {
    $date_paiement = $paiement['date_paiement'];
    $montant       = $paiement['montant_paiement'];
    $mode          = $paiement['mode_paiement'];
    $statut        = $paiement['Statut_paiement'];
    $releve_id     = $paiement['releve_id_releve'];
}
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row mb-3"><div class="col-12"><h3 style="font-weight:700;color:#1a1a2e;"><i class="fas fa-edit mr-2" style="color:#00d2d3;"></i>Modifier Paiement #<?= $id ?></h3></div></div>
<div class="card">
    <div class="card-header" style="background:linear-gradient(135deg,#f093fb,#f5576c);color:white;"><h3 class="card-title"><i class="fas fa-edit mr-2"></i>Modification</h3></div>
    <div class="card-body">
        <?php if (!empty($errors)): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
        <form method="POST">
            <?= csrf_field() ?>
            <div class="row">
                <div class="col-md-8"><div class="form-group"><label>Relevé <span class="text-danger">*</span></label>
                    <select name="releve_id" class="form-control" required><option value="">-- Sélectionner --</option>
                    <?php foreach ($releves as $r): ?><option value="<?= $r['id_releve'] ?>" <?= ($releve_id == $r['id_releve']) ? 'selected' : '' ?>>#<?= $r['id_releve'] ?> — <?= sanitize($r['periode_releve'] . ' | ' . $r['numero_Compteur'] . ' | ' . $r['nom_abonnes'] . ' ' . $r['prenom_abonnes']) ?></option><?php endforeach; ?>
                    </select></div></div>
                <div class="col-md-4"><div class="form-group"><label>Date <span class="text-danger">*</span></label><input type="date" name="date_paiement" class="form-control" value="<?= sanitize($date_paiement) ?>" required></div></div>
            </div>
            <div class="row">
                <div class="col-md-4"><div class="form-group"><label>Montant (€) <span class="text-danger">*</span></label><input type="number" step="0.01" name="montant" class="form-control" value="<?= $montant ?>" required></div></div>
                <div class="col-md-4"><div class="form-group"><label>Mode</label><select name="mode" class="form-control"><option value="Espèces" <?= $mode==='Espèces'?'selected':'' ?>>Espèces</option><option value="Chèque" <?= $mode==='Chèque'?'selected':'' ?>>Chèque</option><option value="Virement" <?= $mode==='Virement'?'selected':'' ?>>Virement</option><option value="Carte bancaire" <?= $mode==='Carte bancaire'?'selected':'' ?>>Carte bancaire</option><option value="Mobile Money" <?= $mode==='Mobile Money'?'selected':'' ?>>Mobile Money</option></select></div></div>
                <div class="col-md-4"><div class="form-group"><label>Statut</label><select name="statut" class="form-control"><option value="Payé" <?= $statut==='Payé'?'selected':'' ?>>Payé</option><option value="En attente" <?= $statut==='En attente'?'selected':'' ?>>En attente</option><option value="Annulé" <?= $statut==='Annulé'?'selected':'' ?>>Annulé</option></select></div></div>
            </div>
            <hr>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Mettre à jour</button>
            <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i> Annuler</a>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
