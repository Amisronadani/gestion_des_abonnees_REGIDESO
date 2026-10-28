<?php
$page_title = 'Modifier Compteur';
$current_page = 'compteurs';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_access('compteurs');

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: index.php'); exit; }
$stmt = $pdo->prepare("SELECT * FROM compteur WHERE id_Compteur = ?");
$stmt->execute([$id]);
$compteur = $stmt->fetch();
if (!$compteur) { set_flash('danger', 'Compteur introuvable.'); header('Location: index.php'); exit; }

$points = $pdo->query("SELECT p.id_Point_de_service, p.libelle_Point_de_service, c.reference_Contrat, a.nom_abonnes FROM point_de_service p LEFT JOIN contrat c ON p.id_Contrat = c.id_Contrat LEFT JOIN abonne a ON c.idabonne_abonnes = a.idabonne_abonnes ORDER BY p.libelle_Point_de_service")->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) $errors[] = 'Token CSRF invalide.';
    $numero    = trim($_POST['numero'] ?? '');
    $type      = trim($_POST['type'] ?? '');
    $date_pose = trim($_POST['date_pose'] ?? '');
    $etat      = trim($_POST['etat'] ?? '');
    $id_point  = (int)($_POST['id_point'] ?? 0);
    if (empty($numero)) $errors[] = 'Le numéro est obligatoire.';
    if ($id_point <= 0) $errors[] = 'Veuillez sélectionner un point de service.';
    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE compteur SET numero_Compteur=?, Typecompteur_Compteur=?, DatePose_Compteur=?, Etat_compteur_Compteur=?, id_Point_de_service=? WHERE id_Compteur=?");
        $stmt->execute([$numero, $type, $date_pose ?: null, $etat, $id_point, $id]);
        set_flash('success', 'Compteur modifié avec succès.');
        header('Location: index.php'); exit;
    }
} else {
    $numero    = $compteur['numero_Compteur'];
    $type      = $compteur['Typecompteur_Compteur'];
    $date_pose = $compteur['DatePose_Compteur'];
    $etat      = $compteur['Etat_compteur_Compteur'];
    $id_point  = $compteur['id_Point_de_service'];
}
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row mb-3"><div class="col-12"><h3 style="font-weight:700;color:#1a1a2e;"><i class="fas fa-edit mr-2" style="color:#54a0ff;"></i>Modifier Compteur #<?= $id ?></h3></div></div>
<div class="card">
    <div class="card-header" style="background:linear-gradient(135deg,#f093fb,#f5576c);color:white;"><h3 class="card-title"><i class="fas fa-edit mr-2"></i>Modification</h3></div>
    <div class="card-body">
        <?php if (!empty($errors)): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
        <form method="POST">
            <?= csrf_field() ?>
            <div class="row">
                <div class="col-md-6"><div class="form-group"><label>Numéro <span class="text-danger">*</span></label><input type="text" name="numero" class="form-control" value="<?= sanitize($numero) ?>" required></div></div>
                <div class="col-md-6"><div class="form-group"><label>Type</label><select name="type" class="form-control"><option value="Mécanique" <?= $type==='Mécanique'?'selected':'' ?>>Mécanique</option><option value="Numérique" <?= $type==='Numérique'?'selected':'' ?>>Numérique</option><option value="Intelligent" <?= $type==='Intelligent'?'selected':'' ?>>Intelligent</option></select></div></div>
            </div>
            <div class="row">
                <div class="col-md-4"><div class="form-group"><label>Date de Pose</label><input type="date" name="date_pose" class="form-control" value="<?= sanitize($date_pose) ?>"></div></div>
                <div class="col-md-4"><div class="form-group"><label>État</label><select name="etat" class="form-control"><option value="Actif" <?= $etat==='Actif'?'selected':'' ?>>Actif</option><option value="Inactif" <?= $etat==='Inactif'?'selected':'' ?>>Inactif</option><option value="En panne" <?= $etat==='En panne'?'selected':'' ?>>En panne</option></select></div></div>
                <div class="col-md-4"><div class="form-group"><label>Point de Service <span class="text-danger">*</span></label>
                    <select name="id_point" class="form-control" required><option value="">-- Sélectionner --</option>
                    <?php foreach ($points as $p): ?><option value="<?= $p['id_Point_de_service'] ?>" <?= ($id_point == $p['id_Point_de_service']) ? 'selected' : '' ?>><?= sanitize($p['libelle_Point_de_service'] . ' (' . $p['reference_Contrat'] . ')') ?></option><?php endforeach; ?>
                    </select></div></div>
            </div>
            <hr>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Mettre à jour</button>
            <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i> Annuler</a>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
