<?php
$page_title = 'Modifier Utilisateur';
$current_page = 'utilisateurs';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_access('utilisateurs');

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: index.php'); exit; }
$stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id_utilisateur = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();
if (!$user) { set_flash('danger', 'Utilisateur introuvable.'); header('Location: index.php'); exit; }

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) $errors[] = 'Token CSRF invalide.';
    $nom    = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $role   = trim($_POST['role'] ?? '');
    $statut = trim($_POST['statut'] ?? '');
    $pass   = $_POST['password'] ?? '';
    $pass2  = $_POST['password2'] ?? '';
    if (empty($nom))    $errors[] = 'Le nom est obligatoire.';
    if (empty($prenom)) $errors[] = 'Le prénom est obligatoire.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email valide obligatoire.';
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM utilisateurs WHERE email_utilisateur = ? AND id_utilisateur != ?");
    $stmt->execute([$email, $id]);
    if ($stmt->fetchColumn() > 0) $errors[] = 'Cet email est déjà utilisé.';
    if (!empty($pass)) {
        if (strlen($pass) < 6) $errors[] = 'Min. 6 caractères.';
        if ($pass !== $pass2)  $errors[] = 'Mots de passe différents.';
    }
    if (empty($errors)) {
        if (!empty($pass)) {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE utilisateurs SET nom_utilisateur=?, prenom_utilisateur=?, email_utilisateur=?, mot_de_passe=?, role_utilisateur=?, statut_utilisateur=? WHERE id_utilisateur=?");
            $stmt->execute([$nom, $prenom, $email, $hash, $role, $statut, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE utilisateurs SET nom_utilisateur=?, prenom_utilisateur=?, email_utilisateur=?, role_utilisateur=?, statut_utilisateur=? WHERE id_utilisateur=?");
            $stmt->execute([$nom, $prenom, $email, $role, $statut, $id]);
        }
        set_flash('success', 'Utilisateur modifié avec succès.');
        header('Location: index.php'); exit;
    }
} else {
    $nom    = $user['nom_utilisateur'];
    $prenom = $user['prenom_utilisateur'];
    $email  = $user['email_utilisateur'];
    $role   = $user['role_utilisateur'];
    $statut = $user['statut_utilisateur'];
}
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row mb-3"><div class="col-12"><h3 style="font-weight:700;color:#1a1a2e;"><i class="fas fa-user-edit mr-2" style="color:#e056fd;"></i>Modifier Utilisateur #<?= $id ?></h3></div></div>
<div class="card">
    <div class="card-header" style="background:linear-gradient(135deg,#f093fb,#f5576c);color:white;"><h3 class="card-title"><i class="fas fa-edit mr-2"></i>Modification</h3></div>
    <div class="card-body">
        <?php if (!empty($errors)): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
        <form method="POST">
            <?= csrf_field() ?>
            <div class="row">
                <div class="col-md-6"><div class="form-group"><label>Nom <span class="text-danger">*</span></label><input type="text" name="nom" class="form-control" value="<?= sanitize($nom) ?>" required></div></div>
                <div class="col-md-6"><div class="form-group"><label>Prénom <span class="text-danger">*</span></label><input type="text" name="prenom" class="form-control" value="<?= sanitize($prenom) ?>" required></div></div>
            </div>
            <div class="row">
                <div class="col-md-6"><div class="form-group"><label>Email <span class="text-danger">*</span></label><input type="email" name="email" class="form-control" value="<?= sanitize($email) ?>" required></div></div>
                <div class="col-md-3"><div class="form-group"><label>Rôle <span class="text-danger">*</span></label><select name="role" class="form-control" required><option value="Utilisateur" <?= $role==='Utilisateur'?'selected':'' ?>>Utilisateur</option><option value="Agent" <?= $role==='Agent'?'selected':'' ?>>Agent</option><option value="Administrateur" <?= $role==='Administrateur'?'selected':'' ?>>Administrateur</option></select></div></div>
                <div class="col-md-3"><div class="form-group"><label>Statut</label><select name="statut" class="form-control"><option value="Actif" <?= $statut==='Actif'?'selected':'' ?>>Actif</option><option value="Inactif" <?= $statut==='Inactif'?'selected':'' ?>>Inactif</option></select></div></div>
            </div>
            <div class="row">
                <div class="col-md-6"><div class="form-group"><label>Nouveau mot de passe <small class="text-muted">(vide = inchangé)</small></label><input type="password" name="password" class="form-control" minlength="6"></div></div>
                <div class="col-md-6"><div class="form-group"><label>Confirmer</label><input type="password" name="password2" class="form-control" minlength="6"></div></div>
            </div>
            <hr>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Mettre à jour</button>
            <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i> Annuler</a>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
