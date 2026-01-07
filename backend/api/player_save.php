<?php
// backend/api/player_save.php
require_once __DIR__ . '/../auth/check_auth.php';
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/response.php';

$input = json_decode(file_get_contents('php://input'), true);

$id             = intval($input['id'] ?? 0);
$team_id        = intval($input['team_id'] ?? 0);
$name           = trim($input['name'] ?? '');
$position       = trim($input['position'] ?? '');
$number         = intval($input['number'] ?? 0);
$birthdate      = trim($input['birthdate'] ?? '');
$photo_url      = trim($input['photo_url'] ?? '');

// CV Specific fields
$is_visible     = !empty($input['is_visible']) ? 1 : 0;
$bio            = trim($input['bio'] ?? '');
$height         = floatval($input['height'] ?? 0);
$weight         = floatval($input['weight'] ?? 0);
$preferred_foot = trim($input['preferred_foot'] ?? '');
$skills         = trim($input['skills'] ?? '');
$video_url      = trim($input['video_url'] ?? '');
$license_number = trim($input['license_number'] ?? '');

// Stats and extras
$matches_played = intval($input['matches_played'] ?? 0);
$goals          = intval($input['goals'] ?? 0);
$assists        = intval($input['assists'] ?? 0);
$instagram      = trim($input['instagram'] ?? '');
$twitter        = trim($input['twitter'] ?? '');
$linkedin       = trim($input['linkedin'] ?? '');
$motto          = trim($input['motto'] ?? '');

if ($team_id <= 0 || $name === '') {
    json_error("team_id et name sont obligatoires.");
}

$pdo = getPDO();

if ($id > 0) {
    // UPDATE
    $stmt = $pdo->prepare("
        UPDATE players 
        SET team_id = ?, name = ?, position = ?, number = ?, birthdate = ?, photo_url = ?,
            is_visible = ?, bio = ?, height = ?, weight = ?, preferred_foot = ?, skills = ?, video_url = ?,
            license_number = ?, matches_played = ?, goals = ?, assists = ?, instagram = ?, twitter = ?, linkedin = ?, motto = ?
        WHERE id = ?
    ");
    $stmt->execute([
        $team_id, $name, $position ?: null, $number ?: null, $birthdate ?: null, $photo_url ?: null,
        $is_visible, $bio ?: null, $height ?: null, $weight ?: null, $preferred_foot ?: null, $skills ?: null, $video_url ?: null,
        $license_number ?: null, $matches_played, $goals, $assists, $instagram ?: null, $twitter ?: null, $linkedin ?: null, $motto ?: null,
        $id
    ]);
    json_ok(['player_id' => $id], "Joueur mis à jour avec succès.");
} else {
    // INSERT
    $stmt = $pdo->prepare("
        INSERT INTO players (team_id, name, position, number, birthdate, photo_url, is_visible, bio, height, weight, preferred_foot, skills, video_url, license_number, matches_played, goals, assists, instagram, twitter, linkedin, motto)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $team_id, $name, $position ?: null, $number ?: null, $birthdate ?: null, $photo_url ?: null,
        $is_visible, $bio ?: null, $height ?: null, $weight ?: null, $preferred_foot ?: null, $skills ?: null, $video_url ?: null,
        $license_number ?: null, $matches_played, $goals, $assists, $instagram ?: null, $twitter ?: null, $linkedin ?: null, $motto ?: null
    ]);
    json_ok(['player_id' => $pdo->lastInsertId()], "Joueur créé avec succès.");
}
