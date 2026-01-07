<?php
require_once "../core/db.php";

$pdo = getPDO();

$username = "admin";
$password = "admin123";
$hash = password_hash($password, PASSWORD_DEFAULT);

// Vérifier si existant
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$username]);

if ($stmt->fetch()) {
    // Update
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE username = ?");
    $stmt->execute([$hash, $username]);
    echo "Mot de passe de l'utilisateur '$username' réinitialisé à '$password'.";
} else {
    // Create
    $stmt = $pdo->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
    $stmt->execute([$username, $hash]);
    echo "Utilisateur créé :<br>User: $username<br>Pass: $password";
}
