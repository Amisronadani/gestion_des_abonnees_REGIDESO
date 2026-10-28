<?php
/**
 * RESIDESO - Supprimer un Abonné (Admin uniquement)
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_access('abonnes');

if (!has_role('Administrateur')) {
    set_flash('danger', 'Seul un administrateur peut supprimer un abonné.');
    header('Location: index.php');
    exit;
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: index.php'); exit; }

// Vérifier les dépendances (contrats liés)
$stmt = $pdo->prepare("SELECT COUNT(*) FROM contrat WHERE idabonne_abonnes = ?");
$stmt->execute([$id]);
$count = $stmt->fetchColumn();

if ($count > 0) {
    set_flash('danger', 'Impossible de supprimer cet abonné : il possède ' . $count . ' contrat(s) associé(s). Supprimez d\'abord les contrats.');
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("DELETE FROM abonne WHERE idabonne_abonnes = ?");
$stmt->execute([$id]);

set_flash('success', 'Abonné supprimé avec succès.');
header('Location: index.php');
exit;
