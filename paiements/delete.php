<?php
/**
 * RESIDESO - Supprimer un Paiement
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_access('paiements');

if (!has_role('Administrateur')) {
    set_flash('danger', 'Seul un administrateur peut supprimer un paiement.');
    header('Location: index.php'); exit;
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: index.php'); exit; }

$stmt = $pdo->prepare("DELETE FROM paiement WHERE id_paiement = ?");
$stmt->execute([$id]);
set_flash('success', 'Paiement supprimé avec succès.');
header('Location: index.php');
exit;
