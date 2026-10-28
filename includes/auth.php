<?php
/**
 * RESIDESO - Gestion de l'authentification et des sessions
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Vérifie si l'utilisateur est connecté
 */
function est_connecte() {
    return isset($_SESSION['user_id']);
}

/**
 * Redirige vers la page de connexion si non connecté
 */
function require_login() {
    if (!est_connecte()) {
        set_flash('danger', 'Veuillez vous connecter pour accéder à cette page.');
        header('Location: ' . BASE_URL . '/auth/login.php');
        exit;
    }
}

/**
 * Vérifie si l'utilisateur a un rôle spécifique
 */
function has_role($role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

/**
 * Vérifie si l'utilisateur a accès à un module donné
 * Utilisateur : abonnes, contrats, points_service, compteurs
 * Agent       : releves, paiements, points_service
 * Admin       : tout
 */
function has_access($module) {
    if (!est_connecte()) return false;
    
    $role = $_SESSION['user_role'] ?? '';
    
    $permissions = [
        'Utilisateur' => ['abonnes', 'contrats', 'points_service', 'compteurs', 'dashboard', 'rapports', 'historique'],
        'Agent'       => ['releves', 'paiements', 'points_service', 'dashboard', 'rapports', 'historique'],
        'Administrateur' => ['abonnes', 'contrats', 'points_service', 'compteurs', 'releves', 'paiements', 'utilisateurs', 'dashboard', 'rapports', 'historique'],
    ];
    
    if (!isset($permissions[$role])) return false;
    
    return in_array($module, $permissions[$role]);
}

/**
 * Restreint l'accès à un module - redirige si non autorisé
 */
function require_access($module) {
    require_login();
    if (!has_access($module)) {
        set_flash('danger', 'Accès refusé. Vous n\'avez pas les permissions nécessaires.');
        header('Location: ' . BASE_URL . '/dashboard/index.php');
        exit;
    }
}

/**
 * Génère un token CSRF
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Vérifie le token CSRF
 */
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Champ hidden CSRF pour les formulaires
 */
function csrf_field() {
    $token = generate_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

/**
 * Messages flash (notifications)
 */
function set_flash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function display_flash() {
    $flash = get_flash();
    if ($flash) {
        echo '<div class="alert alert-' . htmlspecialchars($flash['type']) . ' alert-dismissible fade show">';
        echo '<button type="button" class="close" data-dismiss="alert">&times;</button>';
        echo htmlspecialchars($flash['message']);
        echo '</div>';
    }
}
