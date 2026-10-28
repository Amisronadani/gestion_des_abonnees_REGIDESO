<?php
/**
 * RESIDESO - Créer un Contrat
 */
$page_title = 'Nouveau Contrat';
$current_page = 'contrats';

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_access('contrats');

$abonnes = $pdo->query("SELECT idabonne_abonnes, nom_abonnes, prenom_abonnes FROM abonne ORDER BY nom_abonnes")->fetchAll();
$errors = [];
$reference = $dateDebut = $dateFin = $statut = '';
$idabonne = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) $errors[] = 'Token CSRF invalide.';
    $reference = trim($_POST['reference'] ?? '');
    $dateDebut = trim($_POST['dateDebut'] ?? '');
    $dateFin   = trim($_POST['dateFin'] ?? '');
    $statut    = trim($_POST['statut'] ?? '');
    $idabonne  = (int)($_POST['idabonne'] ?? 0);
    
    if (empty($reference)) $errors[] = 'La référence est obligatoire.';
    if (empty($dateDebut)) $errors[] = 'La date de début est obligatoire.';
    if ($idabonne <= 0)    $errors[] = 'Veuillez sélectionner un abonné.';
    
    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO contrat (reference_Contrat, dateDebut_Contrat, dateFin_Contrat, Statut_Contrat, idabonne_abonnes) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$reference, $dateDebut, $dateFin ?: null, $statut, $idabonne]);
        set_flash('success', 'Contrat créé avec succès.');
        header('Location: index.php');
        exit;
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row mb-3">
    <div class="col-12">
        <h3 style="font-weight: 700; color: #1a1a2e;"><i class="fas fa-plus-circle mr-2" style="color: #feca57;"></i>Nouveau Contrat</h3>
    </div>
</div>

<div class="card">
    <div class="card-header" style="background: linear-gradient(135deg, #667eea, #764ba2); color: white;">
        <h3 class="card-title"><i class="fas fa-edit mr-2"></i>Formulaire de contrat</h3>
    </div>
    <div class="card-body">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?></ul></div>
        <?php endif; ?>
        <form method="POST">
            <?= csrf_field() ?>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Référence <span class="text-danger">*</span></label>
                        <input type="text" name="reference" class="form-control" value="<?= sanitize($reference) ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Abonné <span class="text-danger">*</span></label>
                        <select name="idabonne" class="form-control" required>
                            <option value="">-- Sélectionner un abonné --</option>
                            <?php foreach ($abonnes as $ab): ?>
                                <option value="<?= $ab['idabonne_abonnes'] ?>" <?= ($idabonne == $ab['idabonne_abonnes']) ? 'selected' : '' ?>>
                                    <?= sanitize($ab['nom_abonnes'] . ' ' . $ab['prenom_abonnes']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Date de Début <span class="text-danger">*</span></label>
                        <input type="date" name="dateDebut" class="form-control" value="<?= sanitize($dateDebut) ?>" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Date de Fin</label>
                        <input type="date" name="dateFin" class="form-control" value="<?= sanitize($dateFin) ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Statut</label>
                        <select name="statut" class="form-control">
                            <option value="Actif" <?= $statut === 'Actif' ? 'selected' : '' ?>>Actif</option>
                            <option value="Expiré" <?= $statut === 'Expiré' ? 'selected' : '' ?>>Expiré</option>
                            <option value="Résilié" <?= $statut === 'Résilié' ? 'selected' : '' ?>>Résilié</option>
                        </select>
                    </div>
                </div>
            </div>
            <hr>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Enregistrer</button>
            <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i> Annuler</a>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
