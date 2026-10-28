<?php
$page_title = 'Modifier Point de Service';
$current_page = 'points_service';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_access('points_service');

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: index.php'); exit; }
$stmt = $pdo->prepare("SELECT * FROM point_de_service WHERE id_Point_de_service = ?");
$stmt->execute([$id]);
$point = $stmt->fetch();
if (!$point) { set_flash('danger', 'Point de service introuvable.'); header('Location: index.php'); exit; }

$contrats = $pdo->query("SELECT c.id_Contrat, c.reference_Contrat, a.nom_abonnes, a.prenom_abonnes FROM contrat c LEFT JOIN abonne a ON c.idabonne_abonnes = a.idabonne_abonnes ORDER BY c.reference_Contrat")->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) $errors[] = 'Token CSRF invalide.';
    $libelle    = trim($_POST['libelle'] ?? '');
    $adresse    = trim($_POST['adresse'] ?? '');
    $id_contrat = (int)($_POST['id_contrat'] ?? 0);
    if (empty($libelle))  $errors[] = 'Le libellé est obligatoire.';
    if ($id_contrat <= 0) $errors[] = 'Veuillez sélectionner un contrat.';
    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE point_de_service SET libelle_Point_de_service=?, adresse_Point_de_service=?, id_Contrat=? WHERE id_Point_de_service=?");
        $stmt->execute([$libelle, $adresse, $id_contrat, $id]);
        set_flash('success', 'Point de service modifié avec succès.');
        header('Location: index.php'); exit;
    }
} else {
    $libelle    = $point['libelle_Point_de_service'];
    $adresse    = $point['adresse_Point_de_service'];
    $id_contrat = $point['id_Contrat'];
}
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row mb-3"><div class="col-12"><h3 style="font-weight:700;color:#1a1a2e;"><i class="fas fa-edit mr-2" style="color:#ff6b6b;"></i>Modifier Point de Service #<?= $id ?></h3></div></div>
<div class="card">
    <div class="card-header" style="background:linear-gradient(135deg,#f093fb,#f5576c);color:white;"><h3 class="card-title"><i class="fas fa-edit mr-2"></i>Modification</h3></div>
    <div class="card-body">
        <?php if (!empty($errors)): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
        <form method="POST">
            <?= csrf_field() ?>
            <div class="row">
                <div class="col-md-6"><div class="form-group"><label>Libellé <span class="text-danger">*</span></label><input type="text" name="libelle" class="form-control" value="<?= sanitize($libelle) ?>" required></div></div>
                <div class="col-md-6"><div class="form-group"><label>Adresse</label><input type="text" name="adresse" class="form-control" value="<?= sanitize($adresse) ?>"></div></div>
            </div>
            <div class="row"><div class="col-md-6"><div class="form-group"><label>Contrat <span class="text-danger">*</span></label>
                <select name="id_contrat" class="form-control" required><option value="">-- Sélectionner --</option>
                <?php foreach ($contrats as $c): ?><option value="<?= $c['id_Contrat'] ?>" <?= ($id_contrat == $c['id_Contrat']) ? 'selected' : '' ?>><?= sanitize($c['reference_Contrat'] . ' — ' . $c['nom_abonnes'] . ' ' . $c['prenom_abonnes']) ?></option><?php endforeach; ?>
                </select></div></div></div>
            <hr>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Mettre à jour</button>
            <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i> Annuler</a>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
