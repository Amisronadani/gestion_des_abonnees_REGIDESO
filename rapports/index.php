<?php
/**
 * REGIDESO - Rapports (Journaliers, Semestriels, Annuels)
 * Avec impression et partage
 */
$page_title = 'Rapports';
$current_page = 'rapports';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();
require_access('rapports');

// --- Déterminer le type de rapport ---
$type_rapport = $_GET['type'] ?? 'journalier';
if (!in_array($type_rapport, ['journalier', 'semestriel', 'annuel'])) {
    $type_rapport = 'journalier';
}

// --- Paramètres de date ---
$date_jour = $_GET['date'] ?? date('Y-m-d');
$annee = $_GET['annee'] ?? date('Y');
$semestre = $_GET['semestre'] ?? (date('n') <= 6 ? '1' : '2');

// --- Données du rapport ---
$rapport = [];

if ($type_rapport === 'journalier') {
    // Paiements du jour
    $stmt = $pdo->prepare("SELECT pay.*, r.periode_releve, r.ConsommationCalculee_releve, cm.numero_Compteur, 
        a.nom_abonnes, a.prenom_abonnes, a.type_abonne_abonnes
        FROM paiement pay 
        LEFT JOIN releve r ON pay.releve_id_releve = r.id_releve 
        LEFT JOIN compteur cm ON r.id_Compteur = cm.id_Compteur 
        LEFT JOIN point_de_service p ON cm.id_Point_de_service = p.id_Point_de_service 
        LEFT JOIN contrat c ON p.id_Contrat = c.id_Contrat 
        LEFT JOIN abonne a ON c.idabonne_abonnes = a.idabonne_abonnes 
        WHERE pay.date_paiement = ? ORDER BY pay.id_paiement DESC");
    $stmt->execute([$date_jour]);
    $rapport['paiements'] = $stmt->fetchAll();

    // Statistiques du jour
    $stmt = $pdo->prepare("SELECT COUNT(*) as nb, COALESCE(SUM(montant_paiement),0) as total FROM paiement WHERE date_paiement = ?");
    $stmt->execute([$date_jour]);
    $rapport['stats_paiements'] = $stmt->fetch();

    $stmt = $pdo->prepare("SELECT COUNT(*) as nb, COALESCE(SUM(montant_paiement),0) as total FROM paiement WHERE date_paiement = ? AND Statut_paiement = 'Payé'");
    $stmt->execute([$date_jour]);
    $rapport['stats_payes'] = $stmt->fetch();

    $stmt = $pdo->prepare("SELECT COUNT(*) as nb, COALESCE(SUM(montant_paiement),0) as total FROM paiement WHERE date_paiement = ? AND Statut_paiement != 'Payé'");
    $stmt->execute([$date_jour]);
    $rapport['stats_impayes'] = $stmt->fetch();

    // Relevés du jour
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM releve WHERE DATE(date_releve) = ?");
    $stmt->execute([$date_jour]);
    $rapport['nb_releves'] = $stmt->fetchColumn();

    // Nouveaux abonnés du jour (approximate via contrats)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM contrat WHERE dateDebut_Contrat = ?");
    $stmt->execute([$date_jour]);
    $rapport['nouveaux_contrats'] = $stmt->fetchColumn();

    $titre_rapport = 'Rapport Journalier — ' . date('d/m/Y', strtotime($date_jour));

} elseif ($type_rapport === 'semestriel') {
    if ($semestre == '1') {
        $date_debut = "$annee-01-01";
        $date_fin = "$annee-06-30";
        $label_semestre = "1er Semestre $annee (Janv — Juin)";
    } else {
        $date_debut = "$annee-07-01";
        $date_fin = "$annee-12-31";
        $label_semestre = "2ème Semestre $annee (Juil — Déc)";
    }

    // Paiements du semestre
    $stmt = $pdo->prepare("SELECT pay.*, r.periode_releve, a.nom_abonnes, a.prenom_abonnes 
        FROM paiement pay 
        LEFT JOIN releve r ON pay.releve_id_releve = r.id_releve 
        LEFT JOIN compteur cm ON r.id_Compteur = cm.id_Compteur 
        LEFT JOIN point_de_service p ON cm.id_Point_de_service = p.id_Point_de_service 
        LEFT JOIN contrat c ON p.id_Contrat = c.id_Contrat 
        LEFT JOIN abonne a ON c.idabonne_abonnes = a.idabonne_abonnes 
        WHERE pay.date_paiement BETWEEN ? AND ? ORDER BY pay.date_paiement DESC");
    $stmt->execute([$date_debut, $date_fin]);
    $rapport['paiements'] = $stmt->fetchAll();

    // Stats semestre
    $stmt = $pdo->prepare("SELECT COUNT(*) as nb, COALESCE(SUM(montant_paiement),0) as total FROM paiement WHERE date_paiement BETWEEN ? AND ?");
    $stmt->execute([$date_debut, $date_fin]);
    $rapport['stats_paiements'] = $stmt->fetch();

    $stmt = $pdo->prepare("SELECT COUNT(*) as nb, COALESCE(SUM(montant_paiement),0) as total FROM paiement WHERE date_paiement BETWEEN ? AND ? AND Statut_paiement = 'Payé'");
    $stmt->execute([$date_debut, $date_fin]);
    $rapport['stats_payes'] = $stmt->fetch();

    $stmt = $pdo->prepare("SELECT COUNT(*) as nb, COALESCE(SUM(montant_paiement),0) as total FROM paiement WHERE date_paiement BETWEEN ? AND ? AND Statut_paiement != 'Payé'");
    $stmt->execute([$date_debut, $date_fin]);
    $rapport['stats_impayes'] = $stmt->fetch();

    // Revenus par mois
    $stmt = $pdo->prepare("SELECT MONTH(date_paiement) as mois, COALESCE(SUM(montant_paiement),0) as total, COUNT(*) as nb 
        FROM paiement WHERE date_paiement BETWEEN ? AND ? AND Statut_paiement = 'Payé' GROUP BY MONTH(date_paiement) ORDER BY mois");
    $stmt->execute([$date_debut, $date_fin]);
    $rapport['revenus_mensuels'] = $stmt->fetchAll();

    // Nouveaux contrats
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM contrat WHERE dateDebut_Contrat BETWEEN ? AND ?");
    $stmt->execute([$date_debut, $date_fin]);
    $rapport['nouveaux_contrats'] = $stmt->fetchColumn();

    // Nouveaux relevés
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM releve WHERE DATE(date_releve) BETWEEN ? AND ?");
    $stmt->execute([$date_debut, $date_fin]);
    $rapport['nb_releves'] = $stmt->fetchColumn();

    $titre_rapport = "Rapport Semestriel — $label_semestre";

} else { // annuel
    $date_debut = "$annee-01-01";
    $date_fin = "$annee-12-31";

    // Paiements de l'année - top 50
    $stmt = $pdo->prepare("SELECT pay.*, r.periode_releve, a.nom_abonnes, a.prenom_abonnes 
        FROM paiement pay 
        LEFT JOIN releve r ON pay.releve_id_releve = r.id_releve 
        LEFT JOIN compteur cm ON r.id_Compteur = cm.id_Compteur 
        LEFT JOIN point_de_service p ON cm.id_Point_de_service = p.id_Point_de_service 
        LEFT JOIN contrat c ON p.id_Contrat = c.id_Contrat 
        LEFT JOIN abonne a ON c.idabonne_abonnes = a.idabonne_abonnes 
        WHERE pay.date_paiement BETWEEN ? AND ? ORDER BY pay.date_paiement DESC LIMIT 50");
    $stmt->execute([$date_debut, $date_fin]);
    $rapport['paiements'] = $stmt->fetchAll();

    // Stats annuelles
    $stmt = $pdo->prepare("SELECT COUNT(*) as nb, COALESCE(SUM(montant_paiement),0) as total FROM paiement WHERE date_paiement BETWEEN ? AND ?");
    $stmt->execute([$date_debut, $date_fin]);
    $rapport['stats_paiements'] = $stmt->fetch();

    $stmt = $pdo->prepare("SELECT COUNT(*) as nb, COALESCE(SUM(montant_paiement),0) as total FROM paiement WHERE date_paiement BETWEEN ? AND ? AND Statut_paiement = 'Payé'");
    $stmt->execute([$date_debut, $date_fin]);
    $rapport['stats_payes'] = $stmt->fetch();

    $stmt = $pdo->prepare("SELECT COUNT(*) as nb, COALESCE(SUM(montant_paiement),0) as total FROM paiement WHERE date_paiement BETWEEN ? AND ? AND Statut_paiement != 'Payé'");
    $stmt->execute([$date_debut, $date_fin]);
    $rapport['stats_impayes'] = $stmt->fetch();

    // Revenus par mois
    $stmt = $pdo->prepare("SELECT MONTH(date_paiement) as mois, COALESCE(SUM(montant_paiement),0) as total, COUNT(*) as nb 
        FROM paiement WHERE date_paiement BETWEEN ? AND ? AND Statut_paiement = 'Payé' GROUP BY MONTH(date_paiement) ORDER BY mois");
    $stmt->execute([$date_debut, $date_fin]);
    $rapport['revenus_mensuels'] = $stmt->fetchAll();

    // Nouveaux contrats
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM contrat WHERE dateDebut_Contrat BETWEEN ? AND ?");
    $stmt->execute([$date_debut, $date_fin]);
    $rapport['nouveaux_contrats'] = $stmt->fetchColumn();

    // Nombre d'abonnés total
    $stmt = $pdo->query("SELECT COUNT(*) FROM abonne");
    $rapport['total_abonnes'] = $stmt->fetchColumn();

    // Nombre de relevés
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM releve WHERE DATE(date_releve) BETWEEN ? AND ?");
    $stmt->execute([$date_debut, $date_fin]);
    $rapport['nb_releves'] = $stmt->fetchColumn();

    // Répartition par mode de paiement
    $stmt = $pdo->prepare("SELECT mode_paiement, COUNT(*) as nb, SUM(montant_paiement) as total 
        FROM paiement WHERE date_paiement BETWEEN ? AND ? GROUP BY mode_paiement");
    $stmt->execute([$date_debut, $date_fin]);
    $rapport['par_mode'] = $stmt->fetchAll();

    $titre_rapport = "Rapport Annuel — $annee";
}

$noms_mois = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Titre page -->
<div class="row mb-3 no-print">
    <div class="col-md-8">
        <h3 style="font-weight: 700; color: #1a1a1a;">
            <i class="fas fa-chart-pie mr-2" style="color: #feca57;"></i>Rapports
        </h3>
    </div>
</div>

<!-- Onglets type de rapport -->
<div class="rapport-tabs no-print">
    <a href="?type=journalier" class="rapport-tab <?= $type_rapport === 'journalier' ? 'active' : '' ?>">
        <i class="fas fa-calendar-day"></i>
        <span>Journalier</span>
    </a>
    <a href="?type=semestriel" class="rapport-tab <?= $type_rapport === 'semestriel' ? 'active' : '' ?>">
        <i class="fas fa-calendar-alt"></i>
        <span>Semestriel</span>
    </a>
    <a href="?type=annuel" class="rapport-tab <?= $type_rapport === 'annuel' ? 'active' : '' ?>">
        <i class="fas fa-calendar"></i>
        <span>Annuel</span>
    </a>
</div>

<!-- Filtres de date -->
<div class="card mb-3 no-print">
    <div class="card-body" style="background: linear-gradient(135deg, #fafafa, #fff); border-left: 4px solid #f5c518; border-radius: 12px;">
        <form method="GET" class="form-inline flex-wrap">
            <input type="hidden" name="type" value="<?= $type_rapport ?>">
            
            <?php if ($type_rapport === 'journalier'): ?>
                <div class="form-group mr-3 mb-2">
                    <label class="mr-2" style="font-weight: 600; color: #555;"><i class="fas fa-calendar-day mr-1" style="color: #f5c518;"></i> Date :</label>
                    <input type="date" name="date" class="form-control" value="<?= $date_jour ?>" style="min-width: 200px;">
                </div>
            <?php elseif ($type_rapport === 'semestriel'): ?>
                <div class="form-group mr-3 mb-2">
                    <label class="mr-2" style="font-weight: 600; color: #555;"><i class="fas fa-calendar-alt mr-1" style="color: #f5c518;"></i> Année :</label>
                    <select name="annee" class="form-control" style="min-width: 120px;">
                        <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                            <option value="<?= $y ?>" <?= $annee == $y ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="form-group mr-3 mb-2">
                    <label class="mr-2" style="font-weight: 600; color: #555;"><i class="fas fa-layer-group mr-1" style="color: #e74c3c;"></i> Semestre :</label>
                    <select name="semestre" class="form-control" style="min-width: 180px;">
                        <option value="1" <?= $semestre == '1' ? 'selected' : '' ?>>1er Semestre (Janv - Juin)</option>
                        <option value="2" <?= $semestre == '2' ? 'selected' : '' ?>>2ème Semestre (Juil - Déc)</option>
                    </select>
                </div>
            <?php else: ?>
                <div class="form-group mr-3 mb-2">
                    <label class="mr-2" style="font-weight: 600; color: #555;"><i class="fas fa-calendar mr-1" style="color: #f5c518;"></i> Année :</label>
                    <select name="annee" class="form-control" style="min-width: 120px;">
                        <?php for ($y = date('Y'); $y >= date('Y') - 10; $y--): ?>
                            <option value="<?= $y ?>" <?= $annee == $y ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            <?php endif; ?>

            <button type="submit" class="btn btn-primary mb-2">
                <i class="fas fa-sync-alt mr-1"></i> Générer le rapport
            </button>
        </form>
    </div>
</div>

<!-- Barre d'actions : Imprimer / Partager -->
<div class="rapport-actions no-print">
    <div class="d-flex flex-wrap" style="gap: 10px;">
        <button onclick="imprimerRapport()" class="btn-rapport btn-rapport-print">
            <i class="fas fa-print mr-2"></i> Imprimer le rapport
        </button>
        <button onclick="exporterPDF()" class="btn-rapport btn-rapport-pdf">
            <i class="fas fa-file-pdf mr-2"></i> Exporter PDF
        </button>
        <div class="dropdown">
            <button class="btn-rapport btn-rapport-share dropdown-toggle" data-toggle="dropdown">
                <i class="fas fa-share-alt mr-2"></i> Partager
            </button>
            <div class="dropdown-menu dropdown-menu-right share-dropdown">
                <h6 class="dropdown-header" style="color: #f5c518; font-weight: 700;">
                    <i class="fas fa-share-alt mr-1"></i> Partager ce rapport
                </h6>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item share-item" href="#" onclick="partagerEmail(); return false;">
                    <i class="fas fa-envelope" style="color: #e74c3c;"></i> Par e-mail
                </a>
                <a class="dropdown-item share-item" href="#" onclick="partagerWhatsApp(); return false;">
                    <i class="fab fa-whatsapp" style="color: #25d366;"></i> WhatsApp
                </a>
                <a class="dropdown-item share-item" href="#" onclick="partagerTelegram(); return false;">
                    <i class="fab fa-telegram" style="color: #0088cc;"></i> Telegram
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item share-item" href="#" onclick="telechargerImage(); return false;">
                    <i class="fas fa-download" style="color: #f5c518;"></i> Télécharger l'image
                </a>
                <a class="dropdown-item share-item" href="#" onclick="copierLien(); return false;">
                    <i class="fas fa-link" style="color: #667eea;"></i> Copier le lien
                </a>
            </div>
        </div>
    </div>
</div>

<!-- ============ CONTENU DU RAPPORT (imprimable) ============ -->
<div id="rapport-content">

    <!-- En-tête rapport (visible à l'impression) -->
    <div class="rapport-print-header">
        <div style="text-align: center; padding: 20px 0; border-bottom: 3px solid #f5c518;">
            <img src="<?= BASE_URL ?>/assets/images/logo_regideso.png" alt="REGIDESO" style="width: 60px; height: 60px; border-radius: 50%; object-fit: contain; background: #fff; border: 2px solid #f5c518; padding: 2px;">
            <h2 style="color: #1a1a1a; font-weight: 800; margin: 10px 0 2px; letter-spacing: 2px;">REGIDESO</h2>
            <p style="color: #666; font-size: 0.85rem; margin: 0;">Régie de Production et de Distribution d'Eau et d'Électricité</p>
            <h3 style="color: #e74c3c; font-weight: 700; margin: 15px 0 0;"><?= sanitize($titre_rapport) ?></h3>
            <p style="color: #888; font-size: 0.8rem;">Généré le <?= date('d/m/Y à H:i') ?> par <?= sanitize($_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']) ?></p>
        </div>
    </div>

    <!-- Titre du rapport -->
    <div class="card mb-3">
        <div class="card-header" style="background: linear-gradient(135deg, #1a1a1a, #2d2d2d); color: #f5c518;">
            <h3 class="card-title" style="font-weight: 700; font-size: 1.1rem;">
                <i class="fas fa-file-alt mr-2"></i><?= sanitize($titre_rapport) ?>
            </h3>
            <div class="card-tools">
                <span style="color: rgba(255,255,255,0.5); font-size: 0.8rem;">
                    <i class="fas fa-clock mr-1"></i>Généré le <?= date('d/m/Y à H:i') ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Statistiques résumé -->
    <div class="row mb-3">
        <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #f5c518, #f39c12);">
                <i class="fas fa-money-bill-wave stat-icon"></i>
                <div class="stat-label">Total paiements</div>
                <div class="stat-value"><?= $rapport['stats_paiements']['nb'] ?? 0 ?></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #11998e, #38ef7d);">
                <i class="fas fa-check-circle stat-icon"></i>
                <div class="stat-label">Montant payé</div>
                <div class="stat-value" style="font-size: 1.3rem;"><?= format_montant($rapport['stats_payes']['total'] ?? 0) ?></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #e74c3c, #c0392b);">
                <i class="fas fa-exclamation-circle stat-icon"></i>
                <div class="stat-label">Impayés</div>
                <div class="stat-value" style="font-size: 1.3rem;"><?= format_montant($rapport['stats_impayes']['total'] ?? 0) ?></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                <i class="fas fa-file-invoice stat-icon"></i>
                <div class="stat-label">Revenus totaux</div>
                <div class="stat-value" style="font-size: 1.3rem;"><?= format_montant($rapport['stats_paiements']['total'] ?? 0) ?></div>
            </div>
        </div>
    </div>

    <!-- Indicateurs supplémentaires -->
    <div class="row mb-3">
        <?php if (isset($rapport['nb_releves'])): ?>
        <div class="col-md-4 mb-3">
            <div class="card indicator-card">
                <div class="card-body d-flex align-items-center">
                    <div class="indicator-icon" style="background: linear-gradient(135deg, #4facfe, #00f2fe);">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="ml-3">
                        <div class="indicator-label">Relevés enregistrés</div>
                        <div class="indicator-value"><?= $rapport['nb_releves'] ?></div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php if (isset($rapport['nouveaux_contrats'])): ?>
        <div class="col-md-4 mb-3">
            <div class="card indicator-card">
                <div class="card-body d-flex align-items-center">
                    <div class="indicator-icon" style="background: linear-gradient(135deg, #feca57, #f39c12);">
                        <i class="fas fa-file-contract"></i>
                    </div>
                    <div class="ml-3">
                        <div class="indicator-label">Nouveaux contrats</div>
                        <div class="indicator-value"><?= $rapport['nouveaux_contrats'] ?></div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php if (isset($rapport['total_abonnes'])): ?>
        <div class="col-md-4 mb-3">
            <div class="card indicator-card">
                <div class="card-body d-flex align-items-center">
                    <div class="indicator-icon" style="background: linear-gradient(135deg, #a29bfe, #6c5ce7);">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="ml-3">
                        <div class="indicator-label">Total abonnés</div>
                        <div class="indicator-value"><?= $rapport['total_abonnes'] ?></div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($type_rapport !== 'journalier' && !empty($rapport['revenus_mensuels'])): ?>
    <!-- Graphique revenus mensuels -->
    <div class="card mb-3">
        <div class="card-header" style="background: linear-gradient(135deg, #11998e, #38ef7d); color: white;">
            <h3 class="card-title"><i class="fas fa-chart-bar mr-2"></i>Revenus mensuels</h3>
        </div>
        <div class="card-body">
            <div class="chart-bars">
                <?php 
                $max_val = max(array_column($rapport['revenus_mensuels'], 'total'));
                $max_val = $max_val > 0 ? $max_val : 1;
                foreach ($rapport['revenus_mensuels'] as $rm): 
                    $pct = ($rm['total'] / $max_val) * 100;
                ?>
                <div class="chart-bar-group">
                    <div class="chart-bar-value"><?= format_montant($rm['total']) ?></div>
                    <div class="chart-bar-wrapper">
                        <div class="chart-bar" style="height: <?= max(5, $pct) ?>%;"></div>
                    </div>
                    <div class="chart-bar-label"><?= substr($noms_mois[$rm['mois']] ?? '', 0, 3) ?></div>
                    <div class="chart-bar-count"><?= $rm['nb'] ?> paie.</div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($type_rapport === 'annuel' && !empty($rapport['par_mode'])): ?>
    <!-- Répartition par mode de paiement -->
    <div class="card mb-3">
        <div class="card-header" style="background: linear-gradient(135deg, #667eea, #764ba2); color: white;">
            <h3 class="card-title"><i class="fas fa-wallet mr-2"></i>Répartition par mode de paiement</h3>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Mode de paiement</th>
                        <th>Nombre</th>
                        <th>Montant total</th>
                        <th>Pourcentage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total_general = array_sum(array_column($rapport['par_mode'], 'total'));
                    foreach ($rapport['par_mode'] as $mode): 
                        $pct = $total_general > 0 ? round(($mode['total'] / $total_general) * 100, 1) : 0;
                    ?>
                    <tr>
                        <td><strong><i class="fas fa-credit-card mr-2" style="color: #667eea;"></i><?= sanitize($mode['mode_paiement']) ?></strong></td>
                        <td><span class="badge" style="background: #f5c518; color: #1a1a1a;"><?= $mode['nb'] ?></span></td>
                        <td><strong><?= format_montant($mode['total']) ?></strong></td>
                        <td>
                            <div class="progress" style="height: 20px; border-radius: 10px; background: #f0f0f0;">
                                <div class="progress-bar" style="width: <?= $pct ?>%; background: linear-gradient(135deg, #f5c518, #e74c3c); border-radius: 10px; font-weight: 600; font-size: 0.75rem;">
                                    <?= $pct ?>%
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Tableau détaillé des paiements -->
    <div class="card mb-3">
        <div class="card-header" style="background: linear-gradient(135deg, #f5c518, #f39c12); color: #1a1a1a;">
            <h3 class="card-title"><i class="fas fa-list mr-2"></i>Détail des paiements 
                <span class="badge ml-1" style="background: #1a1a1a; color: #f5c518;"><?= count($rapport['paiements'] ?? []) ?></span>
            </h3>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Abonné</th>
                        <th>Période</th>
                        <th>Montant</th>
                        <th>Mode</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rapport['paiements'])): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>Aucun paiement pour cette période
                        </td></tr>
                    <?php else: foreach ($rapport['paiements'] as $pay): ?>
                    <tr>
                        <td><strong><?= $pay['id_paiement'] ?></strong></td>
                        <td><?= format_date($pay['date_paiement']) ?></td>
                        <td><?= sanitize(($pay['nom_abonnes'] ?? '—') . ' ' . ($pay['prenom_abonnes'] ?? '')) ?></td>
                        <td><span class="badge badge-info"><?= sanitize($pay['periode_releve'] ?? '—') ?></span></td>
                        <td><strong><?= format_montant($pay['montant_paiement']) ?></strong></td>
                        <td><?= sanitize($pay['mode_paiement']) ?></td>
                        <td><span class="badge <?= $pay['Statut_paiement'] === 'Payé' ? 'badge-paye' : 'badge-impaye' ?>"><?= sanitize($pay['Statut_paiement']) ?></span></td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pied de rapport (visible à l'impression) -->
    <div class="rapport-print-footer">
        <div style="border-top: 2px solid #f5c518; padding-top: 15px; margin-top: 30px; text-align: center;">
            <p style="color: #888; font-size: 0.8rem; margin: 0;">
                © <?= date('Y') ?> REGIDESO — Régie de Production et de Distribution d'Eau et d'Électricité · Bujumbura, Burundi
            </p>
            <p style="color: #aaa; font-size: 0.75rem; margin: 5px 0 0;">
                Document généré automatiquement — <?= sanitize($titre_rapport) ?>
            </p>
        </div>
    </div>

</div>

<!-- Modal notification partage -->
<div class="modal fade" id="shareNotifModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; border: none; overflow: hidden;">
            <div class="modal-body text-center py-4" style="background: linear-gradient(135deg, #11998e, #38ef7d);">
                <i class="fas fa-check-circle fa-3x mb-3" style="color: white;"></i>
                <p id="shareNotifText" style="color: white; font-weight: 600; font-size: 1.1rem; margin: 0;"></p>
            </div>
        </div>
    </div>
</div>

<!-- Overlay de capture -->
<div class="capture-overlay" id="captureOverlay" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(26,26,26,0.85);z-index:99998;justify-content:center;align-items:center;flex-direction:column;gap:16px;">
    <div style="width:48px;height:48px;border:4px solid rgba(245,197,24,0.2);border-top-color:#f5c518;border-radius:50%;animation:captSpin 0.8s linear infinite;"></div>
    <div style="color:#f5c518;font-weight:600;font-size:1rem;">📸 Capture en cours...</div>
</div>
<style>@keyframes captSpin { to { transform: rotate(360deg); } }</style>

<script>
var titreRapport = <?= json_encode($titre_rapport) ?>;
var pageUrl = window.location.href;
var captureTarget = document.getElementById('rapport-content');
var fileName = 'REGIDESO_' + titreRapport.replace(/[^a-zA-Z0-9]/g, '_');

function imprimerRapport() { window.print(); }
function exporterPDF() { window.print(); }

// === IMAGE CAPTURE ===
function showLoader() { var o = document.getElementById('captureOverlay'); o.style.display = 'flex'; }
function hideLoader() { document.getElementById('captureOverlay').style.display = 'none'; }

function captureAsImage() {
    return html2canvas(captureTarget, { scale: 2, useCORS: true, allowTaint: true, backgroundColor: '#ffffff', logging: false });
}
function canvasToBlob(canvas) {
    return new Promise(function(resolve) { canvas.toBlob(function(blob) { resolve(blob); }, 'image/png'); });
}
function downloadImage(blob, name) {
    var url = URL.createObjectURL(blob);
    var a = document.createElement('a'); a.href = url; a.download = name;
    document.body.appendChild(a); a.click(); document.body.removeChild(a);
    setTimeout(function() { URL.revokeObjectURL(url); }, 1000);
}

// === SHARE WITH IMAGE ===
function partagerEmail() {
    showLoader();
    captureAsImage().then(function(c) { return canvasToBlob(c); }).then(function(blob) {
        hideLoader();
        var file = new File([blob], fileName + '.png', { type: 'image/png' });
        if (navigator.canShare && navigator.canShare({ files: [file] })) {
            navigator.share({ title: 'REGIDESO — ' + titreRapport, files: [file] }).catch(function(){});
        } else {
            downloadImage(blob, fileName + '.png');
            showShareNotif('📷 Image téléchargée ! Joignez-la à votre e-mail');
            setTimeout(function() {
                var s = encodeURIComponent('REGIDESO — ' + titreRapport);
                var b = encodeURIComponent('Bonjour,\n\nVeuillez trouver en pièce jointe le rapport REGIDESO.\n\n' + titreRapport + '\n\nCordialement,\nREGIDESO');
                window.open('mailto:?subject=' + s + '&body=' + b);
            }, 800);
        }
    }).catch(function() { hideLoader(); });
}

function partagerWhatsApp() {
    showLoader();
    captureAsImage().then(function(c) { return canvasToBlob(c); }).then(function(blob) {
        hideLoader();
        var file = new File([blob], fileName + '.png', { type: 'image/png' });
        if (navigator.canShare && navigator.canShare({ files: [file] })) {
            navigator.share({ title: 'REGIDESO — ' + titreRapport, files: [file] }).catch(function(){});
        } else {
            downloadImage(blob, fileName + '.png');
            showShareNotif('📷 Image téléchargée ! Partagez-la sur WhatsApp');
            setTimeout(function() {
                var t = encodeURIComponent('REGIDESO — ' + titreRapport);
                window.open('https://wa.me/?text=' + t, '_blank');
            }, 800);
        }
    }).catch(function() { hideLoader(); });
}

function partagerTelegram() {
    showLoader();
    captureAsImage().then(function(c) { return canvasToBlob(c); }).then(function(blob) {
        hideLoader();
        var file = new File([blob], fileName + '.png', { type: 'image/png' });
        if (navigator.canShare && navigator.canShare({ files: [file] })) {
            navigator.share({ title: 'REGIDESO — ' + titreRapport, files: [file] }).catch(function(){});
        } else {
            downloadImage(blob, fileName + '.png');
            showShareNotif('📷 Image téléchargée ! Partagez-la sur Telegram');
            setTimeout(function() {
                var t = encodeURIComponent('REGIDESO — ' + titreRapport);
                window.open('https://t.me/share/url?url=' + encodeURIComponent(pageUrl) + '&text=' + t, '_blank');
            }, 800);
        }
    }).catch(function() { hideLoader(); });
}

function telechargerImage() {
    showLoader();
    captureAsImage().then(function(c) { return canvasToBlob(c); }).then(function(blob) {
        hideLoader();
        downloadImage(blob, fileName + '.png');
        showShareNotif('📷 Image du rapport téléchargée !');
    }).catch(function() { hideLoader(); });
}

function copierLien() {
    navigator.clipboard.writeText(pageUrl).then(function() {
        showShareNotif('🔗 Lien copié dans le presse-papiers !');
    }).catch(function() {
        var input = document.createElement('input'); input.value = pageUrl;
        document.body.appendChild(input); input.select(); document.execCommand('copy'); document.body.removeChild(input);
        showShareNotif('🔗 Lien copié !');
    });
}

function showShareNotif(msg) {
    document.getElementById('shareNotifText').innerText = msg;
    $('#shareNotifModal').modal('show');
    setTimeout(function() { $('#shareNotifModal').modal('hide'); }, 2500);
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
