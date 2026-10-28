<?php
/**
 * REGIDESO - Facture professionnelle (HTML)
 * Avec options d'impression et de partage
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_access('paiements');

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: index.php'); exit; }

$stmt = $pdo->prepare("SELECT pay.*, r.periode_releve, r.date_releve, r.indexPrecedent_releve, r.indexSuivant_releve, r.ConsommationCalculee_releve,
    cm.numero_Compteur, cm.Typecompteur_Compteur, p.libelle_Point_de_service, p.adresse_Point_de_service,
    c.reference_Contrat, c.dateDebut_Contrat, c.Statut_Contrat,
    a.nom_abonnes, a.prenom_abonnes, a.telephone_abonnes, a.email_abonnes, a.adresse_abonne, a.type_abonne_abonnes
    FROM paiement pay 
    LEFT JOIN releve r ON pay.releve_id_releve = r.id_releve 
    LEFT JOIN compteur cm ON r.id_Compteur = cm.id_Compteur 
    LEFT JOIN point_de_service p ON cm.id_Point_de_service = p.id_Point_de_service 
    LEFT JOIN contrat c ON p.id_Contrat = c.id_Contrat 
    LEFT JOIN abonne a ON c.idabonne_abonnes = a.idabonne_abonnes 
    WHERE pay.id_paiement = ?");
$stmt->execute([$id]);
$f = $stmt->fetch();
if (!$f) { set_flash('danger', 'Paiement introuvable.'); header('Location: index.php'); exit; }

$num_facture = 'FACT-' . str_pad($f['id_paiement'], 6, '0', STR_PAD_LEFT);
$nom_complet = trim(($f['nom_abonnes'] ?? '') . ' ' . ($f['prenom_abonnes'] ?? ''));
$is_paye = ($f['Statut_paiement'] === 'Payé');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture <?= $num_facture ?> | REGIDESO</title>
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/images/logo_regideso.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
    <style>
        /* Loading overlay */
        .capture-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(26,26,26,0.85);
            z-index: 99998;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            gap: 16px;
        }
        .capture-overlay.show { display: flex; }
        .capture-spinner {
            width: 48px; height: 48px;
            border: 4px solid rgba(245,197,24,0.2);
            border-top-color: #f5c518;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .capture-text { color: #f5c518; font-weight: 600; font-size: 1rem; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; 
            color: #333; 
            background: linear-gradient(135deg, #f0f2f5 0%, #e8ecf1 100%); 
            padding: 20px; 
            min-height: 100vh;
        }

        /* === Action Bar === */
        .action-bar {
            max-width: 820px;
            margin: 0 auto 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }
        .action-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 24px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-family: inherit;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
        }
        .action-btn:hover {
            transform: translateY(-2px);
        }
        .btn-imprimer {
            background: linear-gradient(135deg, #f5c518, #e8a700);
            color: #1a1a1a;
            box-shadow: 0 4px 15px rgba(245,197,24,0.35);
        }
        .btn-imprimer:hover { box-shadow: 0 6px 25px rgba(245,197,24,0.5); }
        .btn-pdf {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            box-shadow: 0 4px 15px rgba(231,76,60,0.3);
        }
        .btn-pdf:hover { box-shadow: 0 6px 25px rgba(231,76,60,0.45); }
        .btn-retour {
            background: linear-gradient(135deg, #636e72, #2d3436);
            color: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
        .btn-retour:hover { box-shadow: 0 6px 20px rgba(0,0,0,0.25); }

        /* Share dropdown */
        .share-wrapper { position: relative; display: inline-block; }
        .btn-partager {
            background: linear-gradient(135deg, #11998e, #38ef7d);
            color: white;
            box-shadow: 0 4px 15px rgba(17,153,142,0.3);
        }
        .btn-partager:hover { box-shadow: 0 6px 25px rgba(17,153,142,0.45); }
        .share-menu {
            display: none;
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            background: white;
            border-radius: 14px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.18);
            min-width: 260px;
            z-index: 1000;
            overflow: hidden;
            animation: slideDown 0.25s ease;
        }
        .share-menu.show { display: block; }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .share-menu-header {
            padding: 14px 20px;
            background: linear-gradient(135deg, #11998e, #38ef7d);
            color: white;
            font-weight: 700;
            font-size: 0.88rem;
        }
        .share-menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: #333;
            text-decoration: none;
            font-size: 0.88rem;
            font-weight: 500;
            transition: all 0.2s;
            border-bottom: 1px solid #f5f5f5;
        }
        .share-menu a:last-child { border-bottom: none; }
        .share-menu a:hover {
            background: rgba(245,197,24,0.06);
            padding-left: 26px;
            color: #1a1a1a;
        }
        .share-menu a i { width: 22px; text-align: center; font-size: 1.1rem; }

        /* === Facture Container === */
        .facture-container {
            max-width: 820px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 40px rgba(0,0,0,0.08);
            overflow: hidden;
            position: relative;
        }

        /* Watermark */
        .facture-container::before {
            content: '<?= $is_paye ? "PAYÉ" : "IMPAYÉ" ?>';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-35deg);
            font-size: 8rem;
            font-weight: 900;
            color: <?= $is_paye ? 'rgba(17,153,142,0.04)' : 'rgba(231,76,60,0.04)' ?>;
            pointer-events: none;
            z-index: 0;
            letter-spacing: 15px;
        }

        .facture-content { position: relative; z-index: 1; padding: 0; }

        /* === Header === */
        .facture-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 35px 45px;
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            border-bottom: 4px solid #f5c518;
        }
        .facture-brand {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .facture-brand img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: contain;
            background: white;
            padding: 3px;
            border: 2px solid #f5c518;
        }
        .facture-brand-text h1 {
            font-size: 1.8rem;
            font-weight: 800;
            color: #f5c518;
            letter-spacing: 3px;
        }
        .facture-brand-text .tagline {
            color: rgba(255,255,255,0.6);
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            margin-top: 2px;
        }
        .facture-id { text-align: right; }
        .facture-id h2 {
            font-size: 1.6rem;
            font-weight: 800;
            color: white;
            letter-spacing: 1px;
        }
        .facture-id .facture-label {
            color: #f5c518;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: 600;
        }

        /* === Status Banner === */
        .status-banner {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 45px;
            background: <?= $is_paye ? 'linear-gradient(135deg, #11998e, #38ef7d)' : 'linear-gradient(135deg, #e74c3c, #c0392b)' ?>;
            color: white;
        }
        .status-badge {
            font-weight: 700;
            font-size: 0.95rem;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .status-date { font-size: 0.85rem; opacity: 0.9; }

        /* === Body === */
        .facture-body { padding: 40px 45px; }

        /* Info sections */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 35px;
        }
        .info-card {
            background: #fafbfc;
            border-radius: 12px;
            padding: 22px 24px;
            border: 1px solid #eef0f2;
            transition: all 0.3s ease;
        }
        .info-card:hover {
            border-color: #f5c518;
            box-shadow: 0 4px 15px rgba(245,197,24,0.08);
        }
        .info-card h3 {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #f5c518;
            font-weight: 700;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .info-card h3 i { font-size: 0.9rem; }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 7px 0;
            font-size: 0.88rem;
            border-bottom: 1px dashed #eee;
        }
        .info-row:last-child { border-bottom: none; }
        .info-label { color: #888; font-weight: 500; }
        .info-value { color: #1a1a1a; font-weight: 600; text-align: right; }

        /* === Detail Table === */
        .detail-section { margin: 30px 0; }
        .detail-section h3 {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #1a1a1a;
            font-weight: 700;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .detail-section h3 i { color: #f5c518; }
        .detail-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        }
        .detail-table thead th {
            background: linear-gradient(135deg, #1a1a1a, #2d2d2d);
            color: #f5c518;
            padding: 14px 20px;
            text-align: left;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            font-weight: 700;
        }
        .detail-table thead th:last-child { text-align: right; }
        .detail-table tbody td {
            padding: 14px 20px;
            font-size: 0.9rem;
            border-bottom: 1px solid #f0f0f0;
            background: white;
        }
        .detail-table tbody td:last-child { text-align: right; }
        .detail-table tbody tr:hover td { background: #fffef5; }
        .detail-table .total-row td {
            font-weight: 800;
            font-size: 1.15rem;
            color: #1a1a1a;
            border-top: 3px solid #f5c518;
            background: linear-gradient(135deg, #fffde6, #fff9cc);
            padding: 18px 20px;
        }
        .montant-highlight {
            font-size: 1.4rem;
            font-weight: 800;
            color: <?= $is_paye ? '#11998e' : '#e74c3c' ?>;
        }

        /* === Footer === */
        .facture-footer {
            background: #fafbfc;
            border-top: 2px solid #f0f0f0;
            padding: 30px 45px;
            text-align: center;
        }
        .footer-brand {
            font-weight: 800;
            color: #1a1a1a;
            font-size: 0.9rem;
            letter-spacing: 1px;
            margin-bottom: 6px;
        }
        .footer-brand span { color: #f5c518; }
        .footer-info { color: #999; font-size: 0.78rem; line-height: 1.8; }
        .footer-legal {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #eee;
            font-size: 0.72rem;
            color: #bbb;
            font-style: italic;
        }

        /* === QR-like reference === */
        .ref-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 25px;
            padding: 18px 22px;
            background: linear-gradient(135deg, #f8f9fa, #f0f2f5);
            border-radius: 12px;
            border: 1px dashed #ddd;
        }
        .ref-code {
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
            color: #555;
            letter-spacing: 1px;
        }
        .ref-label {
            font-size: 0.7rem;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* === Quick notification === */
        .notif-toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 99999;
            background: linear-gradient(135deg, #11998e, #38ef7d);
            color: white;
            padding: 14px 24px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.9rem;
            box-shadow: 0 8px 30px rgba(17,153,142,0.4);
            display: none;
            animation: slideIn 0.3s ease;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(30px); }
            to { opacity: 1; transform: translateX(0); }
        }

        /* === Print === */
        @media print {
            body { background: white; padding: 0; }
            .facture-container { 
                box-shadow: none; border-radius: 0; 
                max-width: 100%;
            }
            .no-print { display: none !important; }
            .facture-header, .status-banner, .detail-table thead th, .detail-table .total-row td {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .facture-container::before {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            @page { margin: 10mm; size: A4; }
        }

        /* === Responsive === */
        @media (max-width: 640px) {
            body { padding: 10px; }
            .facture-header { flex-direction: column; gap: 16px; padding: 25px 20px; text-align: center; }
            .facture-id { text-align: center; }
            .facture-body { padding: 25px 20px; }
            .info-grid { grid-template-columns: 1fr; gap: 16px; }
            .status-banner { flex-direction: column; gap: 6px; padding: 12px 20px; text-align: center; }
            .facture-footer { padding: 20px; }
            .action-bar { justify-content: center; }
            .facture-brand-text h1 { font-size: 1.4rem; }
        }
    </style>
</head>
<body>

<!-- === Barre d'actions === -->
<div class="action-bar no-print">
    <button onclick="window.print()" class="action-btn btn-imprimer">
        <i class="fas fa-print"></i> Imprimer
    </button>
    <button onclick="window.print()" class="action-btn btn-pdf">
        <i class="fas fa-file-pdf"></i> Exporter PDF
    </button>
    <div class="share-wrapper">
        <button class="action-btn btn-partager" onclick="toggleShareMenu()">
            <i class="fas fa-share-alt"></i> Partager <i class="fas fa-chevron-down" style="font-size: 0.7rem; margin-left: 2px;"></i>
        </button>
        <div class="share-menu" id="shareMenu">
            <div class="share-menu-header">
                <i class="fas fa-share-alt mr-1"></i> Partager cette facture
            </div>
            <a href="#" onclick="partagerEmail(); return false;">
                <i class="fas fa-envelope" style="color: #e74c3c;"></i> Envoyer par e-mail
            </a>
            <a href="#" onclick="partagerWhatsApp(); return false;">
                <i class="fab fa-whatsapp" style="color: #25d366;"></i> Partager via WhatsApp
            </a>
            <a href="#" onclick="partagerTelegram(); return false;">
                <i class="fab fa-telegram" style="color: #0088cc;"></i> Partager via Telegram
            </a>
            <a href="#" onclick="telechargerImage(); return false;">
                <i class="fas fa-download" style="color: #f5c518;"></i> Télécharger l'image
            </a>
            <a href="#" onclick="copierLien(); return false;">
                <i class="fas fa-link" style="color: #667eea;"></i> Copier le lien
            </a>
        </div>
    </div>
    <a href="javascript:history.back()" class="action-btn btn-retour">
        <i class="fas fa-arrow-left"></i> Retour
    </a>
</div>

<!-- === Facture === -->
<div class="facture-container">
    <div class="facture-content">

        <!-- En-tête -->
        <div class="facture-header">
            <div class="facture-brand">
                <img src="<?= BASE_URL ?>/assets/images/logo_regideso.png" alt="REGIDESO">
                <div class="facture-brand-text">
                    <h1>REGIDESO</h1>
                    <div class="tagline">Régie de Production et Distribution d'Eau et d'Électricité</div>
                </div>
            </div>
            <div class="facture-id">
                <div class="facture-label">N° Facture</div>
                <h2><?= $num_facture ?></h2>
            </div>
        </div>

        <!-- Bannière statut -->
        <div class="status-banner">
            <div class="status-badge">
                <i class="fas <?= $is_paye ? 'fa-check-circle' : 'fa-exclamation-triangle' ?> mr-2"></i>
                Statut : <?= sanitize($f['Statut_paiement']) ?>
            </div>
            <div class="status-date">
                <i class="fas fa-calendar-alt mr-1"></i>
                Date de paiement : <?= format_date($f['date_paiement']) ?>
            </div>
        </div>

        <!-- Corps -->
        <div class="facture-body">

            <!-- Infos abonné + contrat -->
            <div class="info-grid">
                <div class="info-card">
                    <h3><i class="fas fa-user"></i> Informations Abonné</h3>
                    <div class="info-row">
                        <span class="info-label">Nom complet</span>
                        <span class="info-value"><?= sanitize($nom_complet) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Type</span>
                        <span class="info-value"><?= sanitize($f['type_abonne_abonnes'] ?? '—') ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Adresse</span>
                        <span class="info-value"><?= sanitize($f['adresse_abonne'] ?? '—') ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Téléphone</span>
                        <span class="info-value"><?= sanitize($f['telephone_abonnes'] ?? '—') ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email</span>
                        <span class="info-value"><?= sanitize($f['email_abonnes'] ?? '—') ?></span>
                    </div>
                </div>

                <div class="info-card">
                    <h3><i class="fas fa-file-contract"></i> Informations Contrat</h3>
                    <div class="info-row">
                        <span class="info-label">Référence</span>
                        <span class="info-value"><?= sanitize($f['reference_Contrat'] ?? '—') ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Début contrat</span>
                        <span class="info-value"><?= format_date($f['dateDebut_Contrat'] ?? '') ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Point de service</span>
                        <span class="info-value"><?= sanitize($f['libelle_Point_de_service'] ?? '—') ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">N° Compteur</span>
                        <span class="info-value"><?= sanitize($f['numero_Compteur'] ?? '—') ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Type compteur</span>
                        <span class="info-value"><?= sanitize($f['Typecompteur_Compteur'] ?? '—') ?></span>
                    </div>
                </div>
            </div>

            <!-- Détails de consommation et paiement -->
            <div class="detail-section">
                <h3><i class="fas fa-file-invoice-dollar"></i> Détails de la facturation</h3>
                <table class="detail-table">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th>Détail</th>
                            <th>Valeur</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><i class="fas fa-calendar-week mr-2" style="color: #4facfe;"></i>Période de facturation</td>
                            <td><?= sanitize($f['periode_releve'] ?? '—') ?></td>
                            <td>—</td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-tachometer-alt mr-2" style="color: #feca57;"></i>Index précédent</td>
                            <td>Relevé compteur</td>
                            <td><?= number_format($f['indexPrecedent_releve'] ?? 0, 2, ',', ' ') ?> m³</td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-tachometer-alt mr-2" style="color: #11998e;"></i>Index suivant</td>
                            <td>Relevé compteur</td>
                            <td><?= number_format($f['indexSuivant_releve'] ?? 0, 2, ',', ' ') ?> m³</td>
                        </tr>
                        <tr>
                            <td><strong><i class="fas fa-water mr-2" style="color: #4facfe;"></i>Consommation</strong></td>
                            <td>Volume consommé</td>
                            <td><strong><?= number_format($f['ConsommationCalculee_releve'] ?? 0, 2, ',', ' ') ?> m³</strong></td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-credit-card mr-2" style="color: #a29bfe;"></i>Mode de paiement</td>
                            <td><?= sanitize($f['mode_paiement'] ?? '—') ?></td>
                            <td>—</td>
                        </tr>
                        <tr class="total-row">
                            <td colspan="2">
                                <i class="fas fa-coins mr-2"></i>MONTANT TOTAL À PAYER
                            </td>
                            <td>
                                <span class="montant-highlight"><?= format_montant($f['montant_paiement']) ?></span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Référence de vérification -->
            <div class="ref-section">
                <div>
                    <div class="ref-label">Référence de vérification</div>
                    <div class="ref-code"><?= $num_facture ?> / <?= sanitize($f['reference_Contrat'] ?? '—') ?> / <?= date('Ymd', strtotime($f['date_paiement'])) ?></div>
                </div>
                <div style="text-align: right;">
                    <div class="ref-label">Émis par</div>
                    <div class="ref-code"><?= sanitize($_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']) ?></div>
                </div>
            </div>
        </div>

        <!-- Pied de facture -->
        <div class="facture-footer">
            <div class="footer-brand"><span>REGIDESO</span> — Régie de Production et de Distribution d'Eau et d'Électricité</div>
            <div class="footer-info">
                B.P 660, Bujumbura, Burundi · Tél : +257 22 22 34 53<br>
                NIF : 4000011508 · RC : 170/77
            </div>
            <div class="footer-legal">
                Facture générée le <?= date('d/m/Y à H:i') ?> — Ce document fait foi de reçu de paiement.
            </div>
        </div>
    </div>
</div>

<!-- Notification toast -->
<div class="notif-toast" id="notifToast">
    <i class="fas fa-check-circle mr-2"></i>
    <span id="notifText"></span>
</div>

<!-- Overlay de capture -->
<div class="capture-overlay" id="captureOverlay">
    <div class="capture-spinner"></div>
    <div class="capture-text">📸 Capture en cours...</div>
</div>

<script>
var numFacture = <?= json_encode($num_facture) ?>;
var nomAbonne = <?= json_encode($nom_complet) ?>;
var montant = <?= json_encode(format_montant($f['montant_paiement'])) ?>;
var pageUrl = window.location.href;
var captureTarget = document.querySelector('.facture-container');

// Toggle share menu
function toggleShareMenu() {
    document.getElementById('shareMenu').classList.toggle('show');
}

// Fermer le menu si on clique ailleurs
document.addEventListener('click', function(e) {
    var wrapper = document.querySelector('.share-wrapper');
    if (wrapper && !wrapper.contains(e.target)) {
        document.getElementById('shareMenu').classList.remove('show');
    }
});

// ========== CAPTURE IMAGE ==========
function showLoader() {
    document.getElementById('captureOverlay').classList.add('show');
}
function hideLoader() {
    document.getElementById('captureOverlay').classList.remove('show');
}

function captureAsImage() {
    return html2canvas(captureTarget, {
        scale: 2,
        useCORS: true,
        allowTaint: true,
        backgroundColor: '#ffffff',
        logging: false
    });
}

function canvasToBlob(canvas) {
    return new Promise(function(resolve) {
        canvas.toBlob(function(blob) { resolve(blob); }, 'image/png');
    });
}

function downloadImage(blob, filename) {
    var url = URL.createObjectURL(blob);
    var a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    setTimeout(function() { URL.revokeObjectURL(url); }, 1000);
}

// ========== PARTAGE AVEC IMAGE ==========

// Partage natif (Web Share API avec fichier image)
function partagerNatif() {
    closeShareMenu();
    showLoader();
    captureAsImage().then(function(canvas) {
        return canvasToBlob(canvas);
    }).then(function(blob) {
        hideLoader();
        var file = new File([blob], numFacture + '.png', { type: 'image/png' });
        if (navigator.canShare && navigator.canShare({ files: [file] })) {
            navigator.share({
                title: 'REGIDESO — Facture ' + numFacture,
                text: 'Facture ' + numFacture + ' — ' + nomAbonne + ' — ' + montant,
                files: [file]
            }).catch(function() {});
        } else {
            // Fallback: télécharger l'image
            downloadImage(blob, numFacture + '.png');
            showNotif('Image téléchargée — partagez-la manuellement');
        }
    }).catch(function() { hideLoader(); showNotif('Erreur lors de la capture'); });
}

// Partage par email (télécharge image + ouvre email)
function partagerEmail() {
    closeShareMenu();
    showLoader();
    captureAsImage().then(function(canvas) {
        return canvasToBlob(canvas);
    }).then(function(blob) {
        hideLoader();
        downloadImage(blob, numFacture + '.png');
        showNotif('📷 Image téléchargée ! Joignez-la à votre e-mail');
        setTimeout(function() {
            var subject = encodeURIComponent('REGIDESO — Facture ' + numFacture);
            var body = encodeURIComponent(
                'Bonjour,\n\n' +
                'Veuillez trouver en pièce jointe la facture REGIDESO :\n\n' +
                'Facture : ' + numFacture + '\n' +
                'Abonné : ' + nomAbonne + '\n' +
                'Montant : ' + montant + '\n\n' +
                'Cordialement,\nREGIDESO'
            );
            window.open('mailto:?subject=' + subject + '&body=' + body);
        }, 800);
    }).catch(function() { hideLoader(); });
}

// Partage WhatsApp (télécharge image + ouvre WhatsApp)
function partagerWhatsApp() {
    closeShareMenu();
    showLoader();
    captureAsImage().then(function(canvas) {
        return canvasToBlob(canvas);
    }).then(function(blob) {
        hideLoader();
        var file = new File([blob], numFacture + '.png', { type: 'image/png' });
        // Essayer Web Share API avec fichier
        if (navigator.canShare && navigator.canShare({ files: [file] })) {
            navigator.share({
                title: 'REGIDESO — Facture ' + numFacture,
                files: [file]
            }).catch(function() {});
        } else {
            downloadImage(blob, numFacture + '.png');
            showNotif('📷 Image téléchargée ! Partagez-la sur WhatsApp');
            setTimeout(function() {
                var text = encodeURIComponent(
                    'REGIDESO — Facture ' + numFacture + '\n' +
                    'Abonné : ' + nomAbonne + '\n' +
                    'Montant : ' + montant
                );
                window.open('https://wa.me/?text=' + text, '_blank');
            }, 800);
        }
    }).catch(function() { hideLoader(); });
}

// Partage Telegram (télécharge image + ouvre Telegram)
function partagerTelegram() {
    closeShareMenu();
    showLoader();
    captureAsImage().then(function(canvas) {
        return canvasToBlob(canvas);
    }).then(function(blob) {
        hideLoader();
        var file = new File([blob], numFacture + '.png', { type: 'image/png' });
        if (navigator.canShare && navigator.canShare({ files: [file] })) {
            navigator.share({
                title: 'REGIDESO — Facture ' + numFacture,
                files: [file]
            }).catch(function() {});
        } else {
            downloadImage(blob, numFacture + '.png');
            showNotif('📷 Image téléchargée ! Partagez-la sur Telegram');
            setTimeout(function() {
                var text = encodeURIComponent('REGIDESO — Facture ' + numFacture + ' | ' + nomAbonne + ' | ' + montant);
                var url = encodeURIComponent(pageUrl);
                window.open('https://t.me/share/url?url=' + url + '&text=' + text, '_blank');
            }, 800);
        }
    }).catch(function() { hideLoader(); });
}

// Télécharger l'image
function telechargerImage() {
    closeShareMenu();
    showLoader();
    captureAsImage().then(function(canvas) {
        return canvasToBlob(canvas);
    }).then(function(blob) {
        hideLoader();
        downloadImage(blob, numFacture + '.png');
        showNotif('📷 Image de la facture téléchargée !');
    }).catch(function() { hideLoader(); });
}

// Copier le lien
function copierLien() {
    closeShareMenu();
    if (navigator.clipboard) {
        navigator.clipboard.writeText(pageUrl).then(function() {
            showNotif('🔗 Lien copié dans le presse-papiers !');
        });
    } else {
        var input = document.createElement('input');
        input.value = pageUrl;
        document.body.appendChild(input);
        input.select();
        document.execCommand('copy');
        document.body.removeChild(input);
        showNotif('🔗 Lien copié !');
    }
}

function closeShareMenu() {
    document.getElementById('shareMenu').classList.remove('show');
}

function showNotif(msg) {
    var toast = document.getElementById('notifToast');
    document.getElementById('notifText').innerText = msg;
    toast.style.display = 'flex';
    toast.style.alignItems = 'center';
    toast.style.gap = '8px';
    setTimeout(function() {
        toast.style.transition = 'opacity 0.5s, transform 0.5s';
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(30px)';
        setTimeout(function() {
            toast.style.display = 'none';
            toast.style.opacity = '1';
            toast.style.transform = '';
        }, 500);
    }, 2500);
}
</script>

</body>
</html>
