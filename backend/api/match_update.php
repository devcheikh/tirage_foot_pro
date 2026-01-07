<?php
// backend/api/match_update.php
require_once __DIR__ . '/../auth/check_auth.php';
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/response.php';

$input = json_decode(file_get_contents('php://input'), true);

$id         = intval($input['id'] ?? 0);
$draw_id    = intval($input['draw_id'] ?? 0);
$poule_id   = intval($input['poule_id'] ?? 0);
$team_home  = intval($input['team_home'] ?? 0);
$team_away  = intval($input['team_away'] ?? 0);
$match_date = trim($input['match_date'] ?? '');
$match_time = trim($input['match_time'] ?? '');
$location   = trim($input['location'] ?? '');

if ($id <= 0 || $draw_id <= 0 || $poule_id <= 0 || $team_home <= 0 || $team_away <= 0) {
    json_error("Tous les IDs sont obligatoires pour la mise à jour.");
}

if ($team_home === $team_away) {
    json_error("L'équipe à domicile et l'équipe à l'extérieur doivent être différentes.");
}

try {
    $pdo = getPDO();
    $stmt = $pdo->prepare("
        UPDATE matches 
        SET draw_id = ?, poule_id = ?, team_home = ?, team_away = ?, match_date = ?, match_time = ?, location = ?
        WHERE id = ?
    ");
    $stmt->execute([
        $draw_id,
        $poule_id,
        $team_home,
        $team_away,
        $match_date ?: null,
        $match_time ?: null,
        $location ?: null,
        $id
    ]);

    json_ok(null, "Match mis à jour avec succès.");
} catch (Exception $e) {
    json_error($e->getMessage());
}
