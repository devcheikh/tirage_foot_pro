<?php
// backend/api/players_list.php  Liste des joueurs (par équipe)
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/response.php';

$team_id = intval($_GET['team_id'] ?? 0);

$pdo = getPDO();

if ($team_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM players WHERE team_id = ? ORDER BY number, name");
    $stmt->execute([$team_id]);
} else {
    $stmt = $pdo->query("
        SELECT p.*, t.name AS team_name
        FROM players p
        JOIN teams t ON p.team_id = t.id
        ORDER BY t.name, p.name
    ");
}

$players = $stmt->fetchAll(PDO::FETCH_ASSOC);
json_ok($players);
