<?php
$page_title = 'Nouvel Utilisateur';
$current_page = 'utilisateurs';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_access('utilisateurs');

$errors = [];
$nom = $prenom = $email = $role = $statut = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) $errors[] = 'Token CSRF invalide.';
    $nom     = trim($_POST['nom'] ?? '');
    $prenom  = trim($_POST['prenom'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $pass    = $_POST['password'] ?? '';
    $pass2   = $_POST['password2'] ?? '';
    $role    = trim($_POST['role'] ?? '');
    $statut  = trim($_POST['statut'] ?? '');
    if (empty($nom))    $errors[] = 'Le nom est obligatoire.';
    if (empty($prenom)) $errors[] = 'Le prénom est obligatoire.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email valide obligatoire.';
    if (strlen($pass) < 6) $errors[] = 'Mot de passe : min. 6 caractères.';
    if ($pass !== $pass2)  $errors[] = 'Les mots de passe ne correspondent pas.';
    if (!in_array($role, ['Utilisateur', 'Agent', 'Administrateur'])) $errors[] = 'Rôle invalide.';
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM utilisateurs WHERE email_utilisateur = ?");
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) $errors[] = 'Cet email est déjà utilisé.';
    if (empty($errors)) {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom_utilisateur, prenom_utilisateur, email_utilisateur, mot_de_passe, role_utilisateur, statut_utilisateur) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nom, $prenom, $email, $hash, $role, $statut]);
        set_flash('success', 'Utilisateur créé avec succès.');
        header('Location: index.php'); exit;
    }
}
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row mb-3"><div class="col-12"><h3 style="font-weight:700;color:#1a1a2e;"><i class="fas fa-user-plus mr-2" style="color:#e056fd;"></i>Nouvel Utilisateur</h3></div></div>
<div class="card">
    <div class="card-header" style="background:linear-gradient(135deg,#667eea,#764ba2);color:white;"><h3 class="card-title"><i class="fas fa-edit mr-2"></i>Inscription d'un utilisateur</h3></div>
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
                <div class="col-md-3"><div class="form-group"><label>Rôle <span class="text-danger">*</span></label>
                    <select name="role" class="form-control" required><option value="Utilisateur" <?= $role==='Utilisateur'?'selected':'' ?>>Utilisateur</option><option value="Agent" <?= $role==='Agent'?'selected':'' ?>>Agent</option><option value="Administrateur" <?= $role==='Administrateur'?'selected':'' ?>>Administrateur</option></select></div></div>
                <div class="col-md-3"><div class="form-group"><label>Statut</label>
                    <select name="statut" class="form-control"><option value="Actif" <?= $statut==='Actif'?'selected':'' ?>>Actif</option><option value="Inactif" <?= $statut==='Inactif'?'selected':'' ?>>Inactif</option></select></div></div>
            </div>
            <div class="card mt-3 mb-3" style="border:2px solid #667eea;border-radius:12px;">
                <div class="card-header" style="background:linear-gradient(135deg,#667eea15,#764ba215);border-bottom:1px solid #667eea30;"><h5 class="mb-0" style="color:#667eea;"><i class="fas fa-shield-alt mr-2"></i>Droits d'accès par rôle</h5></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4"><h6 style="color:#667eea;font-weight:700;"><i class="fas fa-user mr-1"></i> Utilisateur</h6><ul class="list-unstyled" style="font-size:0.85rem;"><li><i class="fas fa-check text-success mr-1"></i> Abonnés</li><li><i class="fas fa-check text-success mr-1"></i> Contrats</li><li><i class="fas fa-check text-success mr-1"></i> Points de Service</li><li><i class="fas fa-check text-success mr-1"></i> Compteurs</li><li><i class="fas fa-times text-danger mr-1"></i> Relevés</li><li><i class="fas fa-times text-danger mr-1"></i> Paiements</li><li><i class="fas fa-times text-danger mr-1"></i> Utilisateurs</li></ul></div>
                        <div class="col-md-4"><h6 style="color:#f39c12;font-weight:700;"><i class="fas fa-user-tie mr-1"></i> Agent</h6><ul class="list-unstyled" style="font-size:0.85rem;"><li><i class="fas fa-times text-danger mr-1"></i> Abonnés</li><li><i class="fas fa-times text-danger mr-1"></i> Contrats</li><li><i class="fas fa-check text-success mr-1"></i> Points de Service</li><li><i class="fas fa-times text-danger mr-1"></i> Compteurs</li><li><i class="fas fa-check text-success mr-1"></i> Relevés</li><li><i class="fas fa-check text-success mr-1"></i> Paiements</li><li><i class="fas fa-times text-danger mr-1"></i> Utilisateurs</li></ul></div>
                        <div class="col-md-4"><h6 style="color:#e74c3c;font-weight:700;"><i class="fas fa-user-shield mr-1"></i> Administrateur</h6><ul class="list-unstyled" style="font-size:0.85rem;"><li><i class="fas fa-check text-success mr-1"></i> Abonnés</li><li><i class="fas fa-check text-success mr-1"></i> Contrats</li><li><i class="fas fa-check text-success mr-1"></i> Points de Service</li><li><i class="fas fa-check text-success mr-1"></i> Compteurs</li><li><i class="fas fa-check text-success mr-1"></i> Relevés</li><li><i class="fas fa-check text-success mr-1"></i> Paiements</li><li><i class="fas fa-check text-success mr-1"></i> Utilisateurs</li></ul></div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6"><div class="form-group"><label>Mot de passe <span class="text-danger">*</span> <small class="text-muted">(min. 6)</small></label><input type="password" name="password" class="form-control" required minlength="6"></div></div>
                <div class="col-md-6"><div class="form-group"><label>Confirmer <span class="text-danger">*</span></label><input type="password" name="password2" class="form-control" required minlength="6"></div></div>
            </div>
            <hr>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Créer l'utilisateur</button>
            <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i> Annuler</a>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
