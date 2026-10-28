<?php
/**
 * REGIDESO - Historique des activités
 * Journal complet de toutes les opérations du système
 */
$page_title = 'Historique';
$current_page = 'historique';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();
require_access('historique');

// --- Filtres ---
$filtre_type = $_GET['filtre'] ?? 'tout';
$date_debut = $_GET['date_debut'] ?? date('Y-m-d', strtotime('-30 days'));
$date_fin = $_GET['date_fin'] ?? date('Y-m-d');
$search = trim($_GET['search'] ?? '');

// --- Construire l'historique combiné depuis les tables existantes ---
$historique = [];

// 1) Paiements récents
$sql_pay = "SELECT pay.id_paiement, pay.date_paiement, pay.montant_paiement, pay.mode_paiement, pay.Statut_paiement,
    a.nom_abonnes, a.prenom_abonnes, r.periode_releve
    FROM paiement pay 
    LEFT JOIN releve r ON pay.releve_id_releve = r.id_releve 
    LEFT JOIN compteur cm ON r.id_Compteur = cm.id_Compteur 
    LEFT JOIN point_de_service p ON cm.id_Point_de_service = p.id_Point_de_service 
    LEFT JOIN contrat c ON p.id_Contrat = c.id_Contrat 
    LEFT JOIN abonne a ON c.idabonne_abonnes = a.idabonne_abonnes 
    WHERE pay.date_paiement BETWEEN ? AND ?
    ORDER BY pay.date_paiement DESC, pay.id_paiement DESC";
$stmt = $pdo->prepare($sql_pay);
$stmt->execute([$date_debut, $date_fin]);
$paiements = $stmt->fetchAll();

foreach ($paiements as $p) {
    $historique[] = [
        'date' => $p['date_paiement'],
        'type' => 'paiement',
        'icon' => 'fa-money-bill-wave',
        'color' => $p['Statut_paiement'] === 'Payé' ? '#11998e' : '#e74c3c',
        'bg' => $p['Statut_paiement'] === 'Payé' ? 'rgba(17,153,142,0.1)' : 'rgba(231,76,60,0.1)',
        'titre' => 'Paiement #' . $p['id_paiement'],
        'description' => ($p['nom_abonnes'] ?? '—') . ' ' . ($p['prenom_abonnes'] ?? '') . ' — ' . format_montant($p['montant_paiement']),
        'detail' => 'Mode: ' . ($p['mode_paiement'] ?? '—') . ' | Période: ' . ($p['periode_releve'] ?? '—'),
        'badge' => $p['Statut_paiement'],
        'badge_class' => $p['Statut_paiement'] === 'Payé' ? 'badge-paye' : 'badge-impaye',
    ];
}

// 2) Relevés
$sql_rel = "SELECT r.id_releve, r.date_releve, r.periode_releve, r.ConsommationCalculee_releve, 
    cm.numero_Compteur, a.nom_abonnes, a.prenom_abonnes
    FROM releve r 
    LEFT JOIN compteur cm ON r.id_Compteur = cm.id_Compteur 
    LEFT JOIN point_de_service p ON cm.id_Point_de_service = p.id_Point_de_service 
    LEFT JOIN contrat c ON p.id_Contrat = c.id_Contrat 
    LEFT JOIN abonne a ON c.idabonne_abonnes = a.idabonne_abonnes 
    WHERE DATE(r.date_releve) BETWEEN ? AND ?
    ORDER BY r.date_releve DESC";
$stmt = $pdo->prepare($sql_rel);
$stmt->execute([$date_debut, $date_fin]);
$releves = $stmt->fetchAll();

foreach ($releves as $r) {
    $historique[] = [
        'date' => date('Y-m-d', strtotime($r['date_releve'])),
        'type' => 'releve',
        'icon' => 'fa-chart-line',
        'color' => '#4facfe',
        'bg' => 'rgba(79,172,254,0.1)',
        'titre' => 'Relevé #' . $r['id_releve'],
        'description' => 'Compteur ' . ($r['numero_Compteur'] ?? '—') . ' — ' . ($r['nom_abonnes'] ?? '') . ' ' . ($r['prenom_abonnes'] ?? ''),
        'detail' => 'Période: ' . ($r['periode_releve'] ?? '—') . ' | Consommation: ' . number_format($r['ConsommationCalculee_releve'] ?? 0, 2) . ' m³',
        'badge' => 'Relevé',
        'badge_class' => 'badge-releve',
    ];
}

// 3) Contrats
$sql_cont = "SELECT c.id_Contrat, c.reference_Contrat, c.dateDebut_Contrat, c.Statut_Contrat,
    a.nom_abonnes, a.prenom_abonnes
    FROM contrat c 
    LEFT JOIN abonne a ON c.idabonne_abonnes = a.idabonne_abonnes 
    WHERE c.dateDebut_Contrat BETWEEN ? AND ?
    ORDER BY c.dateDebut_Contrat DESC";
$stmt = $pdo->prepare($sql_cont);
$stmt->execute([$date_debut, $date_fin]);
$contrats = $stmt->fetchAll();

foreach ($contrats as $ct) {
    $historique[] = [
        'date' => $ct['dateDebut_Contrat'],
        'type' => 'contrat',
        'icon' => 'fa-file-contract',
        'color' => '#feca57',
        'bg' => 'rgba(254,202,87,0.1)',
        'titre' => 'Contrat ' . ($ct['reference_Contrat'] ?? '#' . $ct['id_Contrat']),
        'description' => ($ct['nom_abonnes'] ?? '—') . ' ' . ($ct['prenom_abonnes'] ?? ''),
        'detail' => 'Statut: ' . ($ct['Statut_Contrat'] ?? '—'),
        'badge' => $ct['Statut_Contrat'] ?? 'Nouveau',
        'badge_class' => ($ct['Statut_Contrat'] ?? '') === 'Actif' ? 'badge-actif' : 'badge-inactif',
    ];
}

// --- Filtrer par type ---
if ($filtre_type !== 'tout') {
    $historique = array_filter($historique, function($h) use ($filtre_type) {
        return $h['type'] === $filtre_type;
    });
}

// --- Filtrer par recherche ---
if ($search !== '') {
    $search_lower = mb_strtolower($search);
    $historique = array_filter($historique, function($h) use ($search_lower) {
        return mb_strpos(mb_strtolower($h['titre'] . ' ' . $h['description'] . ' ' . $h['detail']), $search_lower) !== false;
    });
}

// --- Trier par date desc ---
usort($historique, function($a, $b) { return strcmp($b['date'], $a['date']); });

// --- Pagination manuelle ---
$total_items = count($historique);
$per_page = 20;
$current_p = max(1, (int)($_GET['page'] ?? 1));
$total_pages = max(1, ceil($total_items / $per_page));
$current_p = min($current_p, $total_pages);
$offset = ($current_p - 1) * $per_page;
$historique_page = array_slice($historique, $offset, $per_page);

// Grouper par date pour la timeline
$grouped = [];
foreach ($historique_page as $h) {
    $grouped[$h['date']][] = $h;
}

// Statistiques
$stats_total = $total_items;
$stats_paiements = count(array_filter($historique, fn($h) => $h['type'] === 'paiement'));
$stats_releves = count(array_filter($historique, fn($h) => $h['type'] === 'releve'));
$stats_contrats = count(array_filter($historique, fn($h) => $h['type'] === 'contrat'));

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Titre page -->
<div class="row mb-3 no-print">
    <div class="col-md-8">
        <h3 style="font-weight: 700; color: #1a1a1a;">
            <i class="fas fa-history mr-2" style="color: #a29bfe;"></i>Historique des activités
        </h3>
    </div>
</div>

<!-- Stats rapides -->
<div class="row mb-3">
    <div class="col-lg-3 col-md-6 col-sm-6 mb-2">
        <div class="stat-card" style="background: linear-gradient(135deg, #a29bfe, #6c5ce7); padding: 16px;">
            <i class="fas fa-stream stat-icon"></i>
            <div class="stat-label">Total activités</div>
            <div class="stat-value" style="font-size: 1.8rem;"><?= $stats_total ?></div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6 mb-2">
        <div class="stat-card" style="background: linear-gradient(135deg, #11998e, #38ef7d); padding: 16px;">
            <i class="fas fa-money-bill-wave stat-icon"></i>
            <div class="stat-label">Paiements</div>
            <div class="stat-value" style="font-size: 1.8rem;"><?= $stats_paiements ?></div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6 mb-2">
        <div class="stat-card" style="background: linear-gradient(135deg, #4facfe, #00f2fe); padding: 16px;">
            <i class="fas fa-chart-line stat-icon"></i>
            <div class="stat-label">Relevés</div>
            <div class="stat-value" style="font-size: 1.8rem;"><?= $stats_releves ?></div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6 mb-2">
        <div class="stat-card" style="background: linear-gradient(135deg, #feca57, #f39c12); padding: 16px;">
            <i class="fas fa-file-contract stat-icon"></i>
            <div class="stat-label">Contrats</div>
            <div class="stat-value" style="font-size: 1.8rem;"><?= $stats_contrats ?></div>
        </div>
    </div>
</div>

<!-- Filtres -->
<div class="card mb-3 no-print">
    <div class="card-body" style="background: linear-gradient(135deg, #fafafa, #fff); border-left: 4px solid #a29bfe; border-radius: 12px;">
        <form method="GET" class="form-inline flex-wrap">
            <div class="form-group mr-3 mb-2">
                <label class="mr-2" style="font-weight: 600; font-size: 0.85rem; color: #555;">
                    <i class="fas fa-filter mr-1" style="color: #a29bfe;"></i>Type :
                </label>
                <select name="filtre" class="form-control" style="min-width: 160px;">
                    <option value="tout" <?= $filtre_type === 'tout' ? 'selected' : '' ?>>Toutes les activités</option>
                    <option value="paiement" <?= $filtre_type === 'paiement' ? 'selected' : '' ?>>💰 Paiements</option>
                    <option value="releve" <?= $filtre_type === 'releve' ? 'selected' : '' ?>>📊 Relevés</option>
                    <option value="contrat" <?= $filtre_type === 'contrat' ? 'selected' : '' ?>>📄 Contrats</option>
                </select>
            </div>
            <div class="form-group mr-3 mb-2">
                <label class="mr-2" style="font-weight: 600; font-size: 0.85rem; color: #555;">
                    <i class="fas fa-calendar mr-1" style="color: #11998e;"></i>Du :
                </label>
                <input type="date" name="date_debut" class="form-control" value="<?= $date_debut ?>">
            </div>
            <div class="form-group mr-3 mb-2">
                <label class="mr-2" style="font-weight: 600; font-size: 0.85rem; color: #555;">Au :</label>
                <input type="date" name="date_fin" class="form-control" value="<?= $date_fin ?>">
            </div>
            <div class="form-group mr-3 mb-2">
                <input type="text" name="search" class="form-control" placeholder="Rechercher..." value="<?= sanitize($search) ?>" style="min-width: 180px;">
            </div>
            <button type="submit" class="btn btn-primary mb-2">
                <i class="fas fa-search mr-1"></i> Filtrer
            </button>
            <a href="index.php" class="btn btn-secondary mb-2 ml-2">
                <i class="fas fa-times mr-1"></i> Reset
            </a>
        </form>
    </div>
</div>

<!-- Boutons actions -->
<div class="rapport-actions no-print mb-3">
    <div class="d-flex flex-wrap" style="gap: 10px;">
        <button onclick="window.print();" class="btn-rapport btn-rapport-print">
            <i class="fas fa-print mr-2"></i> Imprimer
        </button>
        <div class="dropdown">
            <button class="btn-rapport btn-rapport-share dropdown-toggle" data-toggle="dropdown">
                <i class="fas fa-share-alt mr-2"></i> Partager
            </button>
            <div class="dropdown-menu dropdown-menu-right share-dropdown">
                <h6 class="dropdown-header" style="color: #a29bfe; font-weight: 700;">
                    <i class="fas fa-share-alt mr-1"></i> Partager l'historique
                </h6>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item share-item" href="#" onclick="partagerHistEmail(); return false;">
                    <i class="fas fa-envelope" style="color: #e74c3c;"></i> Par e-mail
                </a>
                <a class="dropdown-item share-item" href="#" onclick="partagerHistWhatsApp(); return false;">
                    <i class="fab fa-whatsapp" style="color: #25d366;"></i> WhatsApp
                </a>
                <a class="dropdown-item share-item" href="#" onclick="partagerHistTelegram(); return false;">
                    <i class="fab fa-telegram" style="color: #0088cc;"></i> Telegram
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item share-item" href="#" onclick="telechargerHistImage(); return false;">
                    <i class="fas fa-download" style="color: #f5c518;"></i> Télécharger l'image
                </a>
                <a class="dropdown-item share-item" href="#" onclick="copierHistLien(); return false;">
                    <i class="fas fa-link" style="color: #667eea;"></i> Copier le lien
                </a>
            </div>
        </div>
    </div>
</div>

<!-- ============ CONTENU HISTORIQUE (imprimable) ============ -->
<div id="historique-content">

    <!-- En-tête impression -->
    <div class="rapport-print-header">
        <div style="text-align: center; padding: 20px 0; border-bottom: 3px solid #a29bfe;">
            <img src="<?= BASE_URL ?>/assets/images/logo_regideso.png" alt="REGIDESO" style="width: 60px; height: 60px; border-radius: 50%; object-fit: contain; background: #fff; border: 2px solid #a29bfe; padding: 2px;">
            <h2 style="color: #1a1a1a; font-weight: 800; margin: 10px 0 2px; letter-spacing: 2px;">REGIDESO</h2>
            <p style="color: #666; font-size: 0.85rem; margin: 0;">Historique des activités</p>
            <h3 style="color: #6c5ce7; font-weight: 700; margin: 15px 0 0;">
                Période : <?= date('d/m/Y', strtotime($date_debut)) ?> — <?= date('d/m/Y', strtotime($date_fin)) ?>
            </h3>
            <p style="color: #888; font-size: 0.8rem;">Généré le <?= date('d/m/Y à H:i') ?></p>
        </div>
    </div>

    <!-- Timeline -->
    <?php if (empty($grouped)): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-inbox fa-3x mb-3" style="color: #ddd;"></i>
                <h5 class="text-muted">Aucune activité trouvée pour cette période</h5>
                <p class="text-muted">Modifiez les filtres ou la période pour afficher des résultats.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="timeline-container">
            <?php foreach ($grouped as $date => $events): ?>
            <div class="timeline-date-group">
                <div class="timeline-date-header">
                    <i class="fas fa-calendar-day mr-2"></i>
                    <?= date('d/m/Y', strtotime($date)) ?>
                    <span class="ml-2" style="font-size: 0.8rem; opacity: 0.7;">(<?= count($events) ?> activité<?= count($events) > 1 ? 's' : '' ?>)</span>
                </div>
                
                <?php foreach ($events as $event): ?>
                <div class="timeline-item" style="border-left-color: <?= $event['color'] ?>;">
                    <div class="timeline-icon" style="background: <?= $event['bg'] ?>; color: <?= $event['color'] ?>;">
                        <i class="fas <?= $event['icon'] ?>"></i>
                    </div>
                    <div class="timeline-content">
                        <div class="d-flex justify-content-between align-items-start flex-wrap">
                            <div>
                                <h6 class="timeline-title"><?= sanitize($event['titre']) ?></h6>
                                <p class="timeline-desc"><?= sanitize($event['description']) ?></p>
                                <p class="timeline-detail"><?= sanitize($event['detail']) ?></p>
                            </div>
                            <span class="badge <?= $event['badge_class'] ?>"><?= sanitize($event['badge']) ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="no-print">
        <nav>
            <ul class="pagination pagination-sm justify-content-center mt-3">
                <li class="page-item <?= $current_p <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $current_p - 1 ?>&filtre=<?= $filtre_type ?>&date_debut=<?= $date_debut ?>&date_fin=<?= $date_fin ?>&search=<?= urlencode($search) ?>">&laquo;</a>
                </li>
                <?php for ($i = max(1, $current_p - 2); $i <= min($total_pages, $current_p + 2); $i++): ?>
                <li class="page-item <?= $i === $current_p ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>&filtre=<?= $filtre_type ?>&date_debut=<?= $date_debut ?>&date_fin=<?= $date_fin ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>
                <li class="page-item <?= $current_p >= $total_pages ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $current_p + 1 ?>&filtre=<?= $filtre_type ?>&date_debut=<?= $date_debut ?>&date_fin=<?= $date_fin ?>&search=<?= urlencode($search) ?>">&raquo;</a>
                </li>
            </ul>
        </nav>
        <p class="text-center text-muted small">Page <?= $current_p ?> sur <?= $total_pages ?> (<?= $total_items ?> activités)</p>
    </div>
    <?php endif; ?>

    <!-- Pied impression -->
    <div class="rapport-print-footer">
        <div style="border-top: 2px solid #a29bfe; padding-top: 15px; margin-top: 30px; text-align: center;">
            <p style="color: #888; font-size: 0.8rem; margin: 0;">
                © <?= date('Y') ?> REGIDESO — Historique des activités
            </p>
        </div>
    </div>
</div>

<!-- Modal notification -->
<div class="modal fade" id="histShareModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; border: none; overflow: hidden;">
            <div class="modal-body text-center py-4" style="background: linear-gradient(135deg, #a29bfe, #6c5ce7);">
                <i class="fas fa-check-circle fa-3x mb-3" style="color: white;"></i>
                <p id="histShareText" style="color: white; font-weight: 600; font-size: 1.1rem; margin: 0;"></p>
            </div>
        </div>
    </div>
</div>

<!-- Overlay de capture -->
<div class="capture-overlay" id="captureOverlay" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(26,26,26,0.85);z-index:99998;justify-content:center;align-items:center;flex-direction:column;gap:16px;">
    <div style="width:48px;height:48px;border:4px solid rgba(162,155,254,0.3);border-top-color:#a29bfe;border-radius:50%;animation:captSpin 0.8s linear infinite;"></div>
    <div style="color:#a29bfe;font-weight:600;font-size:1rem;">📸 Capture en cours...</div>
</div>
<style>@keyframes captSpin { to { transform: rotate(360deg); } }</style>

<script>
var histUrl = window.location.href;
var captureTarget = document.getElementById('historique-content');
var fileName = 'REGIDESO_Historique';

// === IMAGE CAPTURE ===
function showLoader() { document.getElementById('captureOverlay').style.display = 'flex'; }
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
function partagerHistEmail() {
    showLoader();
    captureAsImage().then(function(c) { return canvasToBlob(c); }).then(function(blob) {
        hideLoader();
        var file = new File([blob], fileName + '.png', { type: 'image/png' });
        if (navigator.canShare && navigator.canShare({ files: [file] })) {
            navigator.share({ title: 'REGIDESO — Historique', files: [file] }).catch(function(){});
        } else {
            downloadImage(blob, fileName + '.png');
            showHistNotif('📷 Image téléchargée ! Joignez-la à votre e-mail');
            setTimeout(function() {
                var s = encodeURIComponent('REGIDESO — Historique des activités');
                var b = encodeURIComponent('Bonjour,\n\nVeuillez trouver en pièce jointe l\'historique REGIDESO.\n\nCordialement,\nREGIDESO');
                window.open('mailto:?subject=' + s + '&body=' + b);
            }, 800);
        }
    }).catch(function() { hideLoader(); });
}

function partagerHistWhatsApp() {
    showLoader();
    captureAsImage().then(function(c) { return canvasToBlob(c); }).then(function(blob) {
        hideLoader();
        var file = new File([blob], fileName + '.png', { type: 'image/png' });
        if (navigator.canShare && navigator.canShare({ files: [file] })) {
            navigator.share({ title: 'REGIDESO — Historique', files: [file] }).catch(function(){});
        } else {
            downloadImage(blob, fileName + '.png');
            showHistNotif('📷 Image téléchargée ! Partagez-la sur WhatsApp');
            setTimeout(function() {
                window.open('https://wa.me/?text=' + encodeURIComponent('REGIDESO — Historique des activités'), '_blank');
            }, 800);
        }
    }).catch(function() { hideLoader(); });
}

function partagerHistTelegram() {
    showLoader();
    captureAsImage().then(function(c) { return canvasToBlob(c); }).then(function(blob) {
        hideLoader();
        var file = new File([blob], fileName + '.png', { type: 'image/png' });
        if (navigator.canShare && navigator.canShare({ files: [file] })) {
            navigator.share({ title: 'REGIDESO — Historique', files: [file] }).catch(function(){});
        } else {
            downloadImage(blob, fileName + '.png');
            showHistNotif('📷 Image téléchargée ! Partagez-la sur Telegram');
            setTimeout(function() {
                var t = encodeURIComponent('REGIDESO — Historique des activités');
                window.open('https://t.me/share/url?url=' + encodeURIComponent(histUrl) + '&text=' + t, '_blank');
            }, 800);
        }
    }).catch(function() { hideLoader(); });
}

function telechargerHistImage() {
    showLoader();
    captureAsImage().then(function(c) { return canvasToBlob(c); }).then(function(blob) {
        hideLoader();
        downloadImage(blob, fileName + '.png');
        showHistNotif('📷 Image de l\'historique téléchargée !');
    }).catch(function() { hideLoader(); });
}

function copierHistLien() {
    navigator.clipboard.writeText(histUrl).then(function() {
        showHistNotif('🔗 Lien copié dans le presse-papiers !');
    }).catch(function() {
        var input = document.createElement('input'); input.value = histUrl;
        document.body.appendChild(input); input.select(); document.execCommand('copy'); document.body.removeChild(input);
        showHistNotif('🔗 Lien copié !');
    });
}

function showHistNotif(msg) {
    document.getElementById('histShareText').innerText = msg;
    $('#histShareModal').modal('show');
    setTimeout(function() { $('#histShareModal').modal('hide'); }, 2500);
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
