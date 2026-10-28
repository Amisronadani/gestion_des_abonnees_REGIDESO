<?php
/**
 * RESIDESO - Supprimer un Contrat
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_access('contrats');

if (!has_role('Administrateur')) {
    set_flash('danger', 'Seul un administrateur peut supprimer un contrat.');
    header('Location: index.php'); exit;
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: index.php'); exit; }

$stmt = $pdo->prepare("SELECT COUNT(*) FROM point_de_service WHERE id_Contrat = ?");
$stmt->execute([$id]);
if ($stmt->fetchColumn() > 0) {
    set_flash('danger', 'Impossible de supprimer : ce contrat possède des points de service associés.');
    header('Location: index.php'); exit;
}

$stmt = $pdo->prepare("DELETE FROM contrat WHERE id_Contrat = ?");
$stmt->execute([$id]);
set_flash('success', 'Contrat supprimé avec succès.');
header('Location: index.php');
exit;
