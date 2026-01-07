<?php
require_once "../auth/check_auth.php";  // 🔥 TOUJOURS EN PREMIER
require_once "../core/db.php";
require_once "../core/response.php";

$input = json_decode(file_get_contents("php://input"), true);
$name = trim($input["name"] ?? "");
$season = trim($input["season"] ?? "");
$league_logo_url = trim($input["league_logo_url"] ?? "");

// 1. Validation de base
if ($name === "" || $season === "") {
    json_error("Nom et saison sont obligatoires.");
}

// 2. Validation longueur
if (mb_strlen($name) > 50 || mb_strlen($season) > 20) {
    json_error("Le nom ou la saison est trop long (Max 50 chars).");
}

$pdo = getPDO();

// 3. Vérifier doublons
$stmtCheck = $pdo->prepare("SELECT id FROM categories WHERE name = ? AND season = ?");
$stmtCheck->execute([$name, $season]);
if ($stmtCheck->fetch()) {
    json_error("Cette catégorie existe déjà pour cette saison.");
}

try {
    $stmt = $pdo->prepare("INSERT INTO categories (name, season, league_logo_url) VALUES (?, ?, ?)");
    $stmt->execute([$name, $season, $league_logo_url ?: null]);
    json_ok([], "Catégorie ajoutée.");
} catch (PDOException $e) {
    json_error("Erreur base de données : " . $e->getMessage(), 500);
}