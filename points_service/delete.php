<?php
/**
 * RESIDESO - Supprimer un Point de Service
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_access('points_service');

if (!has_role('Administrateur')) {
    set_flash('danger', 'Seul un administrateur peut supprimer un point de service.');
    header('Location: index.php'); exit;
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: index.php'); exit; }

$stmt = $pdo->prepare("SELECT COUNT(*) FROM compteur WHERE id_Point_de_service = ?");
$stmt->execute([$id]);
if ($stmt->fetchColumn() > 0) {
    set_flash('danger', 'Impossible de supprimer : ce point possède des compteurs associés.');
    header('Location: index.php'); exit;
}

$stmt = $pdo->prepare("DELETE FROM point_de_service WHERE id_Point_de_service = ?");
$stmt->execute([$id]);
set_flash('success', 'Point de service supprimé avec succès.');
header('Location: index.php');
exit;
