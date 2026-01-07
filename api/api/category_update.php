<?php
// backend/api/category_update.php
require_once "../auth/check_auth.php";
require_once "../core/db.php";
require_once "../core/response.php";

$input = json_decode(file_get_contents("php://input"), true);

$id = intval($input["id"] ?? 0);
$name = trim($input["name"] ?? "");
$season = trim($input["season"] ?? "");
$league_logo_url = trim($input["league_logo_url"] ?? "");

if ($id <= 0) {
    json_error("ID invalide.");
}

if ($name === "" || $season === "") {
    json_error("Nom et saison sont obligatoires.");
}

if (mb_strlen($name) > 50 || mb_strlen($season) > 20) {
    json_error("Le nom ou la saison est trop long (Max 50 chars).");
}

$pdo = getPDO();

// Vérifier doublons (sauf pour la catégorie en cours de modification)
$stmtCheck = $pdo->prepare("SELECT id FROM categories WHERE name = ? AND season = ? AND id != ?");
$stmtCheck->execute([$name, $season, $id]);
if ($stmtCheck->fetch()) {
    json_error("Cette catégorie existe déjà pour cette saison.");
}

try {
    $stmt = $pdo->prepare("UPDATE categories SET name = ?, season = ?, league_logo_url = ? WHERE id = ?");
    $stmt->execute([$name, $season, $league_logo_url ?: null, $id]);
    json_ok([], "Catégorie mise à jour avec succès.");
} catch (PDOException $e) {
    json_error("Erreur base de données : " . $e->getMessage(), 500);
}
