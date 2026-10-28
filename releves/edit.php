<?php
$page_title = 'Modifier Relevé';
$current_page = 'releves';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_access('releves');

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: index.php'); exit; }
$stmt = $pdo->prepare("SELECT * FROM releve WHERE id_releve = ?");
$stmt->execute([$id]);
$releve = $stmt->fetch();
if (!$releve) { set_flash('danger', 'Relevé introuvable.'); header('Location: index.php'); exit; }

$compteurs = $pdo->query("SELECT cm.id_Compteur, cm.numero_Compteur, p.libelle_Point_de_service, a.nom_abonnes
    FROM compteur cm LEFT JOIN point_de_service p ON cm.id_Point_de_service = p.id_Point_de_service 
    LEFT JOIN contrat c ON p.id_Contrat = c.id_Contrat LEFT JOIN abonne a ON c.idabonne_abonnes = a.idabonne_abonnes ORDER BY cm.numero_Compteur")->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) $errors[] = 'Token CSRF invalide.';
    $periode     = trim($_POST['periode'] ?? '');
    $date_releve = trim($_POST['date_releve'] ?? '');
    $index_prec  = (float)($_POST['index_prec'] ?? 0);
    $index_suiv  = (float)($_POST['index_suiv'] ?? 0);
    $id_compteur = (int)($_POST['id_compteur'] ?? 0);
    if (empty($periode))   $errors[] = 'La période est obligatoire.';
    if ($id_compteur <= 0) $errors[] = 'Veuillez sélectionner un compteur.';
    if ($index_suiv < $index_prec) $errors[] = 'L\'index suivant doit être >= index précédent.';
    $consommation = $index_suiv - $index_prec;
    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE releve SET periode_releve=?, date_releve=?, indexPrecedent_releve=?, indexSuivant_releve=?, ConsommationCalculee_releve=?, id_Compteur=? WHERE id_releve=?");
        $stmt->execute([$periode, $date_releve ?: date('Y-m-d H:i:s'), $index_prec, $index_suiv, $consommation, $id_compteur, $id]);
        set_flash('success', 'Relevé modifié avec succès.');
        header('Location: index.php'); exit;
    }
} else {
    $periode     = $releve['periode_releve'];
    $date_releve = date('Y-m-d\TH:i', strtotime($releve['date_releve']));
    $index_prec  = $releve['indexPrecedent_releve'];
    $index_suiv  = $releve['indexSuivant_releve'];
    $id_compteur = $releve['id_Compteur'];
}
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row mb-3"><div class="col-12"><h3 style="font-weight:700;color:#1a1a2e;"><i class="fas fa-edit mr-2" style="color:#1dd1a1;"></i>Modifier Relevé #<?= $id ?></h3></div></div>
<div class="card">
    <div class="card-header" style="background:linear-gradient(135deg,#f093fb,#f5576c);color:white;"><h3 class="card-title"><i class="fas fa-edit mr-2"></i>Modification</h3></div>
    <div class="card-body">
        <?php if (!empty($errors)): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
        <form method="POST">
            <?= csrf_field() ?>
            <div class="row">
                <div class="col-md-6"><div class="form-group"><label>Compteur <span class="text-danger">*</span></label>
                    <select name="id_compteur" class="form-control" required><option value="">-- Sélectionner --</option>
                    <?php foreach ($compteurs as $cm): ?><option value="<?= $cm['id_Compteur'] ?>" <?= ($id_compteur == $cm['id_Compteur']) ? 'selected' : '' ?>><?= sanitize($cm['numero_Compteur'] . ' — ' . $cm['libelle_Point_de_service'] . ' (' . $cm['nom_abonnes'] . ')') ?></option><?php endforeach; ?>
                    </select></div></div>
                <div class="col-md-3"><div class="form-group"><label>Période <span class="text-danger">*</span></label><input type="text" name="periode" class="form-control" value="<?= sanitize($periode) ?>" required></div></div>
                <div class="col-md-3"><div class="form-group"><label>Date</label><input type="datetime-local" name="date_releve" class="form-control" value="<?= sanitize($date_releve) ?>"></div></div>
            </div>
            <div class="row">
                <div class="col-md-4"><div class="form-group"><label>Index Précédent (m³)</label><input type="number" step="0.01" name="index_prec" class="form-control" value="<?= $index_prec ?>" id="indexPrec"></div></div>
                <div class="col-md-4"><div class="form-group"><label>Index Suivant (m³)</label><input type="number" step="0.01" name="index_suiv" class="form-control" value="<?= $index_suiv ?>" id="indexSuiv"></div></div>
                <div class="col-md-4"><div class="form-group"><label>Consommation</label><input type="text" class="form-control" id="consoCalc" readonly style="background:#e8f5e9;font-weight:bold;color:#2e7d32;"></div></div>
            </div>
            <hr>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Mettre à jour</button>
            <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i> Annuler</a>
        </form>
    </div>
</div>
<script>
document.getElementById('indexPrec').addEventListener('input', calcConso);
document.getElementById('indexSuiv').addEventListener('input', calcConso);
function calcConso() {
    var prec = parseFloat(document.getElementById('indexPrec').value) || 0;
    var suiv = parseFloat(document.getElementById('indexSuiv').value) || 0;
    document.getElementById('consoCalc').value = (Math.max(0, suiv - prec)).toFixed(2) + ' m³';
}
calcConso();
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
