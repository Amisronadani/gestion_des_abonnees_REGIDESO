<?php
/**
 * RESIDESO - Supprimer un Relevé
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_access('releves');

if (!has_role('Administrateur')) {
    set_flash('danger', 'Seul un administrateur peut supprimer un relevé.');
    header('Location: index.php'); exit;
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: index.php'); exit; }

$stmt = $pdo->prepare("SELECT COUNT(*) FROM paiement WHERE releve_id_releve = ?");
$stmt->execute([$id]);
if ($stmt->fetchColumn() > 0) {
    set_flash('danger', 'Impossible de supprimer : ce relevé possède des paiements associés.');
    header('Location: index.php'); exit;
}

$stmt = $pdo->prepare("DELETE FROM releve WHERE id_releve = ?");
$stmt->execute([$id]);
set_flash('success', 'Relevé supprimé avec succès.');
header('Location: index.php');
exit;
