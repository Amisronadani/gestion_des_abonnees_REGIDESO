<?php
/**
 * REGIDESO - Page de Connexion Unique
 * Un seul formulaire pour tous les utilisateurs (agents, utilisateurs, administrateurs)
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if (est_connecte()) {
    header('Location: ' . BASE_URL . '/dashboard/index.php');
    exit;
}

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $errors[] = 'Veuillez remplir tous les champs.';
    } else {
        // Accepter tous les utilisateurs actifs, quel que soit leur rôle
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email_utilisateur = ? AND statut_utilisateur = 'Actif'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['mot_de_passe'])) {
            $_SESSION['user_id']     = $user['id_utilisateur'];
            $_SESSION['user_nom']    = $user['nom_utilisateur'];
            $_SESSION['user_prenom'] = $user['prenom_utilisateur'];
            $_SESSION['user_email']  = $user['email_utilisateur'];
            $_SESSION['user_role']   = $user['role_utilisateur'];
            header('Location: ' . BASE_URL . '/dashboard/index.php');
            exit;
        } else {
            $errors[] = 'Email ou mot de passe incorrect.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Connexion au système de gestion des abonnés REGIDESO - Bujumbura, Burundi">
    <title>Connexion | REGIDESO</title>
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/images/logo_regideso.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/premium.css">
</head>
<body>
    <div class="login-container">
        <!-- Subtle grid overlay -->
        <div class="login-grid-overlay"></div>

        <!-- Animated water drops -->
        <div class="water-drops">
            <div class="drop"></div>
            <div class="drop"></div>
            <div class="drop"></div>
            <div class="drop"></div>
            <div class="drop"></div>
            <div class="drop"></div>
            <div class="drop"></div>
            <div class="drop"></div>
        </div>

        <!-- Login card -->
        <div class="login-wrapper">
            <div class="login-box">
                <!-- Logo section -->
                <div class="login-logo-section">
                    <img src="<?= BASE_URL ?>/assets/images/logo_regideso.png" 
                         alt="REGIDESO Logo" 
                         class="login-logo-img">
                    <h1 class="login-logo-title">REGIDESO</h1>
                    <p class="login-logo-subtitle">Gestion des Abonnés — Bujumbura, Burundi</p>
                </div>

                <hr class="login-divider">

                <!-- Error alert -->
                <?php if (!empty($errors)): ?>
                    <div class="login-alert">
                        <?php foreach ($errors as $e): ?>
                            <p><i class="fas fa-exclamation-circle"></i> <?= sanitize($e) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Login form — unique pour tous les utilisateurs -->
                <form method="POST" class="login-form" id="loginForm">
                    <div class="login-field-group">
                        <label class="login-field-label" for="login-email">
                            <i class="fas fa-envelope"></i> Adresse email
                        </label>
                        <input type="email" 
                               name="email" 
                               id="login-email"
                               class="login-input" 
                               value="<?= sanitize($email) ?>" 
                               required 
                               placeholder="votre@email.com" 
                               autocomplete="email"
                               autofocus>
                    </div>

                    <div class="login-field-group">
                        <label class="login-field-label" for="login-password">
                            <i class="fas fa-lock"></i> Mot de passe
                        </label>
                        <div class="login-password-wrapper">
                            <input type="password" 
                                   name="password" 
                                   id="login-password"
                                   class="login-input" 
                                   required 
                                   placeholder="••••••••"
                                   autocomplete="current-password">
                            <button type="button" class="login-password-toggle" id="togglePassword" aria-label="Afficher le mot de passe">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="login-submit-btn" id="loginSubmitBtn">
                        <i class="fas fa-sign-in-alt"></i> Se connecter
                    </button>
                </form>

                <!-- Footer — pas de lien vers admin, page unique -->
                <p class="login-copyright">
                    &copy; <?= date('Y') ?> REGIDESO — B.P 660 Bujumbura, Burundi
                </p>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        const toggleBtn = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('login-password');
        
        toggleBtn.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });

        // Button loading state on submit
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('loginSubmitBtn');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Connexion en cours...';
            btn.disabled = true;
            btn.style.opacity = '0.8';
        });
    </script>
</body>
</html>
