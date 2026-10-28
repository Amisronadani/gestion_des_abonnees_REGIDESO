<?php
/**
 * RESIDESO - Supprimer un Compteur
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_access('compteurs');

if (!has_role('Administrateur')) {
    set_flash('danger', 'Seul un administrateur peut supprimer un compteur.');
    header('Location: index.php'); exit;
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: index.php'); exit; }

$stmt = $pdo->prepare("SELECT COUNT(*) FROM releve WHERE id_Compteur = ?");
$stmt->execute([$id]);
if ($stmt->fetchColumn() > 0) {
    set_flash('danger', 'Impossible de supprimer : ce compteur possède des relevés associés.');
    header('Location: index.php'); exit;
}

$stmt = $pdo->prepare("DELETE FROM compteur WHERE id_Compteur = ?");
$stmt->execute([$id]);
set_flash('success', 'Compteur supprimé avec succès.');
header('Location: index.php');
exit;
