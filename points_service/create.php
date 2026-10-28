<?php
$page_title = 'Nouveau Point de Service';
$current_page = 'points_service';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_access('points_service');

$contrats = $pdo->query("SELECT c.id_Contrat, c.reference_Contrat, a.nom_abonnes, a.prenom_abonnes FROM contrat c LEFT JOIN abonne a ON c.idabonne_abonnes = a.idabonne_abonnes WHERE c.Statut_Contrat = 'Actif' ORDER BY c.reference_Contrat")->fetchAll();
$errors = [];
$libelle = $adresse = '';
$id_contrat = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) $errors[] = 'Token CSRF invalide.';
    $libelle    = trim($_POST['libelle'] ?? '');
    $adresse    = trim($_POST['adresse'] ?? '');
    $id_contrat = (int)($_POST['id_contrat'] ?? 0);
    if (empty($libelle))   $errors[] = 'Le libellé est obligatoire.';
    if ($id_contrat <= 0)  $errors[] = 'Veuillez sélectionner un contrat.';
    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO point_de_service (libelle_Point_de_service, adresse_Point_de_service, id_Contrat) VALUES (?, ?, ?)");
        $stmt->execute([$libelle, $adresse, $id_contrat]);
        set_flash('success', 'Point de service créé avec succès.');
        header('Location: index.php'); exit;
    }
}
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row mb-3"><div class="col-12"><h3 style="font-weight:700;color:#1a1a2e;"><i class="fas fa-plus-circle mr-2" style="color:#ff6b6b;"></i>Nouveau Point de Service</h3></div></div>
<div class="card">
    <div class="card-header" style="background:linear-gradient(135deg,#667eea,#764ba2);color:white;"><h3 class="card-title"><i class="fas fa-edit mr-2"></i>Formulaire</h3></div>
    <div class="card-body">
        <?php if (!empty($errors)): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
        <form method="POST">
            <?= csrf_field() ?>
            <div class="row">
                <div class="col-md-6"><div class="form-group"><label>Libellé <span class="text-danger">*</span></label><input type="text" name="libelle" class="form-control" value="<?= sanitize($libelle) ?>" required placeholder="Ex: Point A - Centre-ville"></div></div>
                <div class="col-md-6"><div class="form-group"><label>Adresse</label><input type="text" name="adresse" class="form-control" value="<?= sanitize($adresse) ?>" placeholder="Adresse du point"></div></div>
            </div>
            <div class="row"><div class="col-md-6"><div class="form-group"><label>Contrat associé <span class="text-danger">*</span></label>
                <select name="id_contrat" class="form-control" required><option value="">-- Sélectionner --</option>
                <?php foreach ($contrats as $c): ?><option value="<?= $c['id_Contrat'] ?>" <?= ($id_contrat == $c['id_Contrat']) ? 'selected' : '' ?>><?= sanitize($c['reference_Contrat'] . ' — ' . $c['nom_abonnes'] . ' ' . $c['prenom_abonnes']) ?></option><?php endforeach; ?>
                </select></div></div></div>
            <hr>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Enregistrer</button>
            <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i> Annuler</a>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
