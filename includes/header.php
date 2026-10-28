<?php
/**
 * REGIDESO - Premium Modern Header
 * Vanilla CSS Architecture with Dark Mode
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

$current_page = $current_page ?? '';
$page_title = $page_title ?? 'REGIDESO';
?>
<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= sanitize($page_title) ?> | REGIDESO</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/images/logo_regideso.png">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom Premium Styles -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/premium.css">
</head>
<body>

<div class="premium-wrapper">
    
    <!-- ====== MODERN SIDEBAR ====== -->
    <aside class="sidebar">
        <div class="brand">
            <img src="<?= BASE_URL ?>/assets/images/logo_regideso.png" alt="Logo" style="width: 40px; height: 40px; object-fit: contain; background: #fff; border-radius: 50%; padding: 4px;">
            <span>REGIDESO</span>
        </div>

        <nav class="nav-menu">
            <div class="nav-category">Principal</div>
            <a href="<?= BASE_URL ?>/dashboard/index.php" class="nav-link <?= $current_page === 'dashboard' ? 'active' : '' ?>">
                <i class="fas fa-th-large"></i> Dashboard
            </a>

            <?php if (has_access('abonnes')): ?>
            <div class="nav-category">Gestion Abonnés</div>
            <a href="<?= BASE_URL ?>/abonnes/index.php" class="nav-link <?= $current_page === 'abonnes' ? 'active' : '' ?>">
                <i class="fas fa-users"></i> Abonnés
            </a>
            <?php endif; ?>

            <?php if (has_access('contrats')): ?>
            <a href="<?= BASE_URL ?>/contrats/index.php" class="nav-link <?= $current_page === 'contrats' ? 'active' : '' ?>">
                <i class="fas fa-file-signature"></i> Contrats
            </a>
            <?php endif; ?>

            <?php if (has_access('releves')): ?>
            <div class="nav-category">Consommation</div>
            <a href="<?= BASE_URL ?>/releves/index.php" class="nav-link <?= $current_page === 'releves' ? 'active' : '' ?>">
                <i class="fas fa-chart-line"></i> Relevés
            </a>
            <?php endif; ?>

            <?php if (has_access('paiements')): ?>
            <a href="<?= BASE_URL ?>/paiements/index.php" class="nav-link <?= $current_page === 'paiements' ? 'active' : '' ?>">
                <i class="fas fa-wallet"></i> Paiements
            </a>
            <?php endif; ?>

            <?php if (has_access('rapports') || has_access('historique')): ?>
            <div class="nav-category">Analyse</div>
            <?php if (has_access('rapports')): ?>
            <a href="<?= BASE_URL ?>/rapports/index.php" class="nav-link <?= $current_page === 'rapports' ? 'active' : '' ?>">
                <i class="fas fa-file-alt"></i> Rapports
            </a>
            <?php endif; ?>
            <?php if (has_access('historique')): ?>
            <a href="<?= BASE_URL ?>/historique/index.php" class="nav-link <?= $current_page === 'historique' ? 'active' : '' ?>">
                <i class="fas fa-history"></i> Historique
            </a>
            <?php endif; ?>
            <?php endif; ?>

            <?php if (has_access('utilisateurs')): ?>
            <div class="nav-category">Système</div>
            <a href="<?= BASE_URL ?>/utilisateurs/index.php" class="nav-link <?= $current_page === 'utilisateurs' ? 'active' : '' ?>">
                <i class="fas fa-user-shield"></i> Utilisateurs
            </a>
            <?php endif; ?>
        </nav>

        <div style="margin-top: auto; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.05);">
            <a href="<?= BASE_URL ?>/auth/logout.php" class="nav-link" style="color: #ff6b6b;">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
        </div>
    </aside>

    <!-- ====== MAIN CONTENT AREA ====== -->
    <main class="main-content">
        
        <!-- TOP NAVBAR -->
        <header class="top-navbar">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <button id="sidebar-toggle" class="btn" style="background: transparent; color: var(--text-main); font-size: 1.2rem;">
                    <i class="fas fa-bars"></i>
                </button>
                <h2 style="font-weight: 800; font-size: 1.25rem; letter-spacing: -0.5px;"><?= sanitize($page_title) ?></h2>
            </div>

            <div style="display: flex; align-items: center; gap: 1.5rem;">
                <!-- Theme Toggle -->
                <button id="theme-toggle" class="theme-toggle">
                    <i class="fas fa-moon"></i>
                </button>

                <!-- User Profile -->
                <div style="display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem; border-radius: var(--radius-xl); background: var(--glass);" class="glass-panel">
                    <div style="width: 35px; height: 35px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--secondary)); display: flex; align-items: center; justify-content: center; color: var(--text-on-primary); font-weight: 800; font-size: 0.8rem;">
                        <?= strtoupper(substr($_SESSION['user_prenom'] ?? 'U', 0, 1)) ?>
                    </div>
                    <div style="line-height: 1;">
                        <div style="font-weight: 700; font-size: 0.85rem;"><?= sanitize($_SESSION['user_prenom'] ?? '') ?></div>
                        <div style="font-size: 0.7rem; color: var(--text-muted);"><?= sanitize($_SESSION['user_role'] ?? '') ?></div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Dynamic Page Content -->
        <div class="container-fluid animate-in" style="padding-top: 1.5rem;">
            <?php display_flash(); ?>
