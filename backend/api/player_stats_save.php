<?php
// backend/api/player_stats_save.php
require_once __DIR__ . '/../auth/check_auth.php';
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/response.php';

$input = json_decode(file_get_contents('php://input'), true);

$match_id = intval($input['match_id'] ?? 0);
$player_id = intval($input['player_id'] ?? 0);
$goals = intval($input['goals'] ?? 0);
$assists = intval($input['assists'] ?? 0);
$yellow = intval($input['yellow_cards'] ?? 0);
$red = intval($input['red_cards'] ?? 0);
$minutes = intval($input['minutes_played'] ?? 0);

if ($match_id <= 0 || $player_id <= 0) {
    json_error("match_id et player_id sont obligatoires.");
}

$pdo = getPDO();

// Vérifier s'il existe déjà une ligne pour ce match/joueur
$stmt = $pdo->prepare("
    SELECT id FROM player_stats
    WHERE match_id = ? AND player_id = ?
");
$stmt->execute([$match_id, $player_id]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existing) {
    $stmtUp = $pdo->prepare("
        UPDATE player_stats
        SET goals = ?, assists = ?, yellow_cards = ?, red_cards = ?, minutes_played = ?
        WHERE id = ?
    ");
    $stmtUp->execute([$goals, $assists, $yellow, $red, $minutes, $existing['id']]);
} else {
    $stmtIns = $pdo->prepare("
        INSERT INTO player_stats
        (match_id, player_id, goals, assists, yellow_cards, red_cards, minutes_played)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmtIns->execute([$match_id, $player_id, $goals, $assists, $yellow, $red, $minutes]);
}

json_ok(null, "Statistiques mises à jour.");
