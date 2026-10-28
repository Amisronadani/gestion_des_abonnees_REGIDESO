<?php
$pdo = new PDO('mysql:host=localhost;dbname=gestion_abonnes;charset=utf8mb4', 'root', '');
$hash = password_hash('admin123', PASSWORD_DEFAULT);
$pdo->prepare('UPDATE utilisateurs SET mot_de_passe = ? WHERE email_utilisateur = ?')->execute([$hash, 'admin@resideso.com']);
echo "Admin password updated successfully!\nEmail: admin@resideso.com\nPassword: admin123\nHash: $hash\n";
