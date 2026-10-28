<?php
/**
 * RESIDESO - Supprimer un Utilisateur
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_access('utilisateurs');

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: index.php'); exit; }

// Empêcher la suppression de son propre compte
if ($id == $_SESSION['user_id']) {
    set_flash('danger', 'Vous ne pouvez pas supprimer votre propre compte.');
    header('Location: index.php'); exit;
}

$stmt = $pdo->prepare("DELETE FROM utilisateurs WHERE id_utilisateur = ?");
$stmt->execute([$id]);
set_flash('success', 'Utilisateur supprimé avec succès.');
header('Location: index.php');
exit;
