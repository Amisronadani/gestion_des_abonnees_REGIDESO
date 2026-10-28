<?php
/**
 * RESIDESO - Point d'entrée
 * Redirige vers le dashboard si connecté, sinon vers le login
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

if (est_connecte()) {
    header('Location: ' . BASE_URL . '/dashboard/index.php');
} else {
    header('Location: ' . BASE_URL . '/auth/login.php');
}
exit;
