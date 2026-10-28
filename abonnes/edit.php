<?php
/**
 * RESIDESO - Modifier un Abonné
 */
$page_title = 'Modifier Abonné';
$current_page = 'abonnes';

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_access('abonnes');

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: index.php'); exit; }

$stmt = $pdo->prepare("SELECT * FROM abonne WHERE idabonne_abonnes = ?");
$stmt->execute([$id]);
$abonne = $stmt->fetch();

if (!$abonne) {
    set_flash('danger', 'Abonné introuvable.');
    header('Location: index.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) $errors[] = 'Token CSRF invalide.';
    
    $nom     = trim($_POST['nom'] ?? '');
    $prenom  = trim($_POST['prenom'] ?? '');
    $type    = trim($_POST['type'] ?? '');
    $tel     = trim($_POST['telephone'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');
    
    if (empty($nom))    $errors[] = 'Le nom est obligatoire.';
    if (empty($prenom)) $errors[] = 'Le prénom est obligatoire.';
    if (empty($type))   $errors[] = 'Le type d\'abonné est obligatoire.';
    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email invalide.';
    
    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE abonne SET nom_abonnes=?, prenom_abonnes=?, type_abonne_abonnes=?, telephone_abonnes=?, email_abonnes=?, adresse_abonne=? WHERE idabonne_abonnes=?");
        $stmt->execute([$nom, $prenom, $type, $tel, $email, $adresse, $id]);
        set_flash('success', 'Abonné modifié avec succès.');
        header('Location: index.php');
        exit;
    }
} else {
    $nom     = $abonne['nom_abonnes'];
    $prenom  = $abonne['prenom_abonnes'];
    $type    = $abonne['type_abonne_abonnes'];
    $tel     = $abonne['telephone_abonnes'];
    $email   = $abonne['email_abonnes'];
    $adresse = $abonne['adresse_abonne'];
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row mb-3">
    <div class="col-12">
        <h3 style="font-weight: 700; color: #1a1a2e;">
            <i class="fas fa-user-edit mr-2" style="color: #48dbfb;"></i>Modifier l'Abonné #<?= $id ?>
        </h3>
    </div>
</div>

<div class="card">
    <div class="card-header" style="background: linear-gradient(135deg, #f093fb, #f5576c); color: white;">
        <h3 class="card-title"><i class="fas fa-edit mr-2"></i>Modification</h3>
    </div>
    <div class="card-body">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <?= csrf_field() ?>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Nom <span class="text-danger">*</span></label>
                        <input type="text" name="nom" class="form-control" value="<?= sanitize($nom) ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Prénom <span class="text-danger">*</span></label>
                        <input type="text" name="prenom" class="form-control" value="<?= sanitize($prenom) ?>" required>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Type d'abonné <span class="text-danger">*</span></label>
                        <select name="type" class="form-control" required>
                            <option value="">-- Sélectionner --</option>
                            <option value="Particulier" <?= $type === 'Particulier' ? 'selected' : '' ?>>Particulier</option>
                            <option value="Professionnel" <?= $type === 'Professionnel' ? 'selected' : '' ?>>Professionnel</option>
                            <option value="Industriel" <?= $type === 'Industriel' ? 'selected' : '' ?>>Industriel</option>
                            <option value="Administration" <?= $type === 'Administration' ? 'selected' : '' ?>>Administration</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Téléphone</label>
                        <input type="text" name="telephone" class="form-control" value="<?= sanitize($tel) ?>">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" value="<?= sanitize($email) ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Adresse</label>
                        <input type="text" name="adresse" class="form-control" value="<?= sanitize($adresse) ?>">
                    </div>
                </div>
            </div>
            <hr>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Mettre à jour</button>
            <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i> Annuler</a>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
