<?php
// backend/api/players_delete.php Supprimer un joueur
require_once __DIR__ . '/../auth/check_auth.php';
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/response.php';

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    json_error("id obligatoire.");
}

$pdo = getPDO();

// Optionnel : supprimer aussi ses stats
$stmt = $pdo->prepare("DELETE FROM player_stats WHERE player_id = ?");
$stmt->execute([$id]);

$stmt = $pdo->prepare("DELETE FROM players WHERE id = ?");
$stmt->execute([$id]);

json_ok(null, "Joueur supprimé.");
