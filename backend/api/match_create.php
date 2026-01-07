<?php
// backend/api/match_create.php
require_once __DIR__ . '/../auth/check_auth.php';
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/response.php';

$input = json_decode(file_get_contents('php://input'), true);

$draw_id    = intval($input['draw_id'] ?? 0);
$poule_id   = intval($input['poule_id'] ?? 0);
$team_home  = intval($input['team_home'] ?? 0);
$team_away  = intval($input['team_away'] ?? 0);
$match_date = trim($input['match_date'] ?? '');
$match_time = trim($input['match_time'] ?? '');
$location   = trim($input['location'] ?? '');

if ($draw_id <= 0 || $poule_id <= 0 || $team_home <= 0 || $team_away <= 0) {
    json_error("draw_id, poule_id, team_home et team_away sont obligatoires.");
}

if ($team_home === $team_away) {
    json_error("L'équipe à domicile et l'équipe à l'extérieur doivent être différentes.");
}

$pdo = getPDO();

// Optionnel : vérifier que les équipes appartiennent à la même poule/catégorie
// (à implémenter plus tard si tu veux verrouiller à fond)

$stmt = $pdo->prepare("
    INSERT INTO matches (draw_id, poule_id, team_home, team_away, match_date, match_time, location)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");
$stmt->execute([
    $draw_id,
    $poule_id,
    $team_home,
    $team_away,
    $match_date ?: null,
    $match_time ?: null,
    $location ?: null
]);

$match_id = $pdo->lastInsertId();

// S'assurer qu'une ligne standings existe pour chaque équipe de la poule
// (pratique pour le classement)
$teams = [$team_home, $team_away];
$stmtCheck = $pdo->prepare("SELECT id FROM standings WHERE poule_id = ? AND team_id = ?");
$stmtInsert = $pdo->prepare("
    INSERT INTO standings (poule_id, team_id)
    VALUES (?, ?)
");

foreach ($teams as $tid) {
    $stmtCheck->execute([$poule_id, $tid]);
    if (!$stmtCheck->fetch()) {
        $stmtInsert->execute([$poule_id, $tid]);
    }
}

json_ok(['match_id' => $match_id], "Match créé avec succès.");
