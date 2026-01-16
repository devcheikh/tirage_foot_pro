<?php
// backend/api/players_add.php Ajouter un joueur
require_once __DIR__ . '/../auth/check_auth.php';
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/response.php';

$input = json_decode(file_get_contents('php://input'), true);

$team_id   = intval($input['team_id'] ?? 0);
$name      = trim($input['name'] ?? '');
$position  = trim($input['position'] ?? '');
$number    = intval($input['number'] ?? 0);
$birthdate = trim($input['birthdate'] ?? '');
$photo_url = trim($input['photo_url'] ?? '');

if ($team_id <= 0 || $name === '') {
    json_error("team_id et name sont obligatoires.");
}

$pdo = getPDO();
$stmt = $pdo->prepare("
    INSERT INTO players (team_id, name, position, number, birthdate, photo_url)
    VALUES (?, ?, ?, ?, ?, ?)
");
$stmt->execute([
    $team_id,
    $name,
    $position ?: null,
    $number ?: null,
    $birthdate ?: null,
    $photo_url ?: null
]);

json_ok(['player_id' => $pdo->lastInsertId()], "Joueur créé avec succès.");
