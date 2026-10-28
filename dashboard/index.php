<?php
/**
 * REGIDESO - Premium Dashboard
 */
$page_title = 'Tableau de bord';
$current_page = 'dashboard';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();

// Statistiques
$stats = [];
$stmt = $pdo->query("SELECT COUNT(*) FROM abonne"); $stats['abonnes'] = $stmt->fetchColumn();
$stmt = $pdo->query("SELECT COUNT(*) FROM contrat"); $stats['contrats'] = $stmt->fetchColumn();
$stmt = $pdo->query("SELECT COUNT(*) FROM compteur"); $stats['compteurs'] = $stmt->fetchColumn();
$stmt = $pdo->query("SELECT COUNT(*) FROM point_de_service"); $stats['points'] = $stmt->fetchColumn();
$stmt = $pdo->query("SELECT COUNT(*) FROM releve"); $stats['releves'] = $stmt->fetchColumn();
$stmt = $pdo->query("SELECT COUNT(*) FROM paiement"); $stats['paiements'] = $stmt->fetchColumn();
$stmt = $pdo->query("SELECT COALESCE(SUM(montant_paiement), 0) FROM paiement WHERE Statut_paiement = 'Payé'"); $stats['total_revenus'] = $stmt->fetchColumn();
$stmt = $pdo->query("SELECT COUNT(*) FROM utilisateurs"); $stats['utilisateurs'] = $stmt->fetchColumn();

// Derniers abonnés
$derniers_abonnes = $pdo->query("SELECT * FROM abonne ORDER BY idabonne_abonnes DESC LIMIT 5")->fetchAll();
// Derniers paiements
$derniers_paiements = $pdo->query("SELECT pay.*, r.periode_releve, a.nom_abonnes, a.prenom_abonnes 
    FROM paiement pay 
    LEFT JOIN releve r ON pay.releve_id_releve = r.id_releve 
    LEFT JOIN compteur cm ON r.id_Compteur = cm.id_Compteur 
    LEFT JOIN point_de_service p ON cm.id_Point_de_service = p.id_Point_de_service 
    LEFT JOIN contrat c ON p.id_Contrat = c.id_Contrat 
    LEFT JOIN abonne a ON c.idabonne_abonnes = a.idabonne_abonnes 
    ORDER BY pay.id_paiement DESC LIMIT 5")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div style="margin-bottom: 2rem;">
            <h1 style="font-weight: 800; font-size: 2.2rem; letter-spacing: -1px; margin-bottom: 0.5rem;">
                 Bienvenue, <?= sanitize($_SESSION['user_prenom'] ?? 'Utilisateur') ?>
            </h1>
            <p style="color: var(--text-muted); font-size: 1.1rem;">Voici un aperçu de l'activité de la REGIDESO aujourd'hui.</p>
        </div>
    </div>
</div>

<!-- Primary Stats Grid -->
<div class="row">
    <?php if (has_access('abonnes')): ?>
    <div class="col-3">
        <div class="stat-card" style="background: linear-gradient(135deg, #f5c518, #f39c12);">
            <i class="fas fa-users stat-icon"></i>
            <div class="stat-label">Total Abonnés</div>
            <div class="stat-value"><?= number_format($stats['abonnes']) ?></div>
            <div style="font-size: 0.8rem; opacity: 0.8;">Inscrits dans le système</div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (has_access('contrats')): ?>
    <div class="col-3">
        <div class="stat-card" style="background: linear-gradient(135deg, #e74c3c, #c0392b);">
            <i class="fas fa-file-contract stat-icon"></i>
            <div class="stat-label">Contrats Actifs</div>
            <div class="stat-value"><?= number_format($stats['contrats']) ?></div>
            <div style="font-size: 0.8rem; opacity: 0.8;">Validés et en cours</div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (has_access('releves')): ?>
    <div class="col-3">
        <div class="stat-card" style="background: linear-gradient(135deg, #4facfe, #00f2fe);">
            <i class="fas fa-chart-line stat-icon"></i>
            <div class="stat-label">Relevés Effectués</div>
            <div class="stat-value"><?= number_format($stats['releves']) ?></div>
            <div style="font-size: 0.8rem; opacity: 0.8;">Compteurs relevés</div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (has_access('paiements')): ?>
    <div class="col-3">
        <div class="stat-card" style="background: linear-gradient(135deg, #11998e, #38ef7d);">
            <i class="fas fa-money-bill-wave stat-icon"></i>
            <div class="stat-label">Revenus Encaissés</div>
            <div class="stat-value" style="font-size: 1.6rem;"><?= format_montant($stats['total_revenus']) ?></div>
            <div style="font-size: 0.8rem; opacity: 0.8;">Total paiements validés</div>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="row" style="margin-top: 1rem;">
    <!-- Recent Activity Tables -->
    <?php if (has_access('abonnes')): ?>
    <div class="col-6">
        <div class="card glass-panel">
            <div class="card-header">
                <h3><i class="fas fa-user-plus mr-2" style="color: var(--primary);"></i> Nouveaux Abonnés</h3>
                <a href="<?= BASE_URL ?>/abonnes/index.php" class="btn btn-primary" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">Voir tout</a>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Abonné</th>
                                <th>Type</th>
                                <th>Contact</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($derniers_abonnes)): ?>
                                <tr><td colspan="3" style="text-align: center; padding: 2rem; color: var(--text-muted);">Aucun abonné récent</td></tr>
                            <?php else: foreach ($derniers_abonnes as $ab): ?>
                            <tr>
                                <td>
                                    <div style="font-weight: 700;"><?= sanitize($ab['nom_abonnes'] . ' ' . $ab['prenom_abonnes']) ?></div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);">ID: #<?= $ab['idabonne_abonnes'] ?></div>
                                </td>
                                <td><span class="badge badge-primary"><?= sanitize($ab['type_abonne_abonnes']) ?></span></td>
                                <td><?= sanitize($ab['telephone_abonnes']) ?></td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (has_access('paiements')): ?>
    <div class="col-6">
        <div class="card glass-panel">
            <div class="card-header">
                <h3><i class="fas fa-receipt mr-2" style="color: var(--accent);"></i> Flux de Paiements</h3>
                <a href="<?= BASE_URL ?>/paiements/index.php" class="btn btn-primary" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">Historique</a>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Abonné</th>
                                <th>Montant</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($derniers_paiements)): ?>
                                <tr><td colspan="3" style="text-align: center; padding: 2rem; color: var(--text-muted);">Aucun paiement enregistré</td></tr>
                            <?php else: foreach ($derniers_paiements as $pay): ?>
                            <tr>
                                <td>
                                    <div style="font-weight: 700;"><?= sanitize(($pay['nom_abonnes'] ?? '—') . ' ' . ($pay['prenom_abonnes'] ?? '')) ?></div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);">Période: <?= sanitize($pay['periode_releve'] ?? '—') ?></div>
                                </td>
                                <td style="font-weight: 800; color: var(--text-main);"><?= format_montant($pay['montant_paiement']) ?></td>
                                <td>
                                    <span class="badge <?= $pay['Statut_paiement'] === 'Payé' ? 'badge-success' : 'badge-danger' ?>">
                                        <?= sanitize($pay['Statut_paiement']) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="row" style="margin-top: 1rem;">
    <!-- Secondary Stats cards (Smaller) -->
    <?php if (has_access('compteurs')): ?>
    <div class="col-3">
        <div class="card glass-panel" style="padding: 1.5rem; text-align: center;">
            <div style="font-size: 2rem; color: var(--primary); margin-bottom: 0.5rem;"><i class="fas fa-bolt"></i></div>
            <div style="font-size: 1.5rem; font-weight: 800;"><?= number_format($stats['compteurs']) ?></div>
            <div style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase;">Compteurs</div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (has_access('points_service')): ?>
    <div class="col-3">
        <div class="card glass-panel" style="padding: 1.5rem; text-align: center;">
            <div style="font-size: 2rem; color: #ff6b6b; margin-bottom: 0.5rem;"><i class="fas fa-map-marker-alt"></i></div>
            <div style="font-size: 1.5rem; font-weight: 800;"><?= number_format($stats['points']) ?></div>
            <div style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase;">Points de Service</div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (has_access('utilisateurs')): ?>
    <div class="col-3" style="grid-column: span 6;">
        <div class="card glass-panel" style="padding: 1.5rem; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 1rem;">
                 <div style="font-size: 2rem; color: #e056fd;"><i class="fas fa-user-shield"></i></div>
                 <div>
                     <div style="font-size: 1.5rem; font-weight: 800;"><?= number_format($stats['utilisateurs']) ?></div>
                     <div style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase;">Équipe Administrative</div>
                 </div>
            </div>
            <a href="<?= BASE_URL ?>/utilisateurs/index.php" class="btn btn-outline" style="padding: 0.5rem 1rem;">Gérer</a>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
