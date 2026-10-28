<?php
$page_title = 'Nouveau Compteur';
$current_page = 'compteurs';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_access('compteurs');

$points = $pdo->query("SELECT p.id_Point_de_service, p.libelle_Point_de_service, c.reference_Contrat, a.nom_abonnes, a.prenom_abonnes
    FROM point_de_service p LEFT JOIN contrat c ON p.id_Contrat = c.id_Contrat LEFT JOIN abonne a ON c.idabonne_abonnes = a.idabonne_abonnes ORDER BY p.libelle_Point_de_service")->fetchAll();
$errors = [];
$numero = $type = $date_pose = $etat = '';
$id_point = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) $errors[] = 'Token CSRF invalide.';
    $numero    = trim($_POST['numero'] ?? '');
    $type      = trim($_POST['type'] ?? '');
    $date_pose = trim($_POST['date_pose'] ?? '');
    $etat      = trim($_POST['etat'] ?? '');
    $id_point  = (int)($_POST['id_point'] ?? 0);
    if (empty($numero)) $errors[] = 'Le numéro de compteur est obligatoire.';
    if ($id_point <= 0) $errors[] = 'Veuillez sélectionner un point de service.';
    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO compteur (numero_Compteur, Typecompteur_Compteur, DatePose_Compteur, Etat_compteur_Compteur, id_Point_de_service) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$numero, $type, $date_pose ?: null, $etat, $id_point]);
        set_flash('success', 'Compteur créé avec succès.');
        header('Location: index.php'); exit;
    }
}
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row mb-3"><div class="col-12"><h3 style="font-weight:700;color:#1a1a2e;"><i class="fas fa-plus-circle mr-2" style="color:#54a0ff;"></i>Nouveau Compteur</h3></div></div>
<div class="card">
    <div class="card-header" style="background:linear-gradient(135deg,#667eea,#764ba2);color:white;"><h3 class="card-title"><i class="fas fa-edit mr-2"></i>Formulaire</h3></div>
    <div class="card-body">
        <?php if (!empty($errors)): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
        <form method="POST">
            <?= csrf_field() ?>
            <div class="row">
                <div class="col-md-6"><div class="form-group"><label>Numéro <span class="text-danger">*</span></label><input type="text" name="numero" class="form-control" value="<?= sanitize($numero) ?>" required placeholder="Ex: CPT-001"></div></div>
                <div class="col-md-6"><div class="form-group"><label>Type</label><select name="type" class="form-control"><option value="Mécanique" <?= $type==='Mécanique'?'selected':'' ?>>Mécanique</option><option value="Numérique" <?= $type==='Numérique'?'selected':'' ?>>Numérique</option><option value="Intelligent" <?= $type==='Intelligent'?'selected':'' ?>>Intelligent</option></select></div></div>
            </div>
            <div class="row">
                <div class="col-md-4"><div class="form-group"><label>Date de Pose</label><input type="date" name="date_pose" class="form-control" value="<?= sanitize($date_pose ?: date('Y-m-d')) ?>"></div></div>
                <div class="col-md-4"><div class="form-group"><label>État</label><select name="etat" class="form-control"><option value="Actif" <?= $etat==='Actif'?'selected':'' ?>>Actif</option><option value="Inactif" <?= $etat==='Inactif'?'selected':'' ?>>Inactif</option><option value="En panne" <?= $etat==='En panne'?'selected':'' ?>>En panne</option></select></div></div>
                <div class="col-md-4"><div class="form-group"><label>Point de Service <span class="text-danger">*</span></label>
                    <select name="id_point" class="form-control" required><option value="">-- Sélectionner --</option>
                    <?php foreach ($points as $p): ?><option value="<?= $p['id_Point_de_service'] ?>" <?= ($id_point == $p['id_Point_de_service']) ? 'selected' : '' ?>><?= sanitize($p['libelle_Point_de_service'] . ' (' . $p['reference_Contrat'] . ' - ' . $p['nom_abonnes'] . ')') ?></option><?php endforeach; ?>
                    </select></div></div>
            </div>
            <hr>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Enregistrer</button>
            <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i> Annuler</a>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
