<?php
// backend/api/get_standings.php Récupérer le classement d’une poule
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/response.php';

$poule_id = intval($_GET['poule_id'] ?? 0);

if ($poule_id <= 0) {
    json_error("poule_id obligatoire.");
}

$pdo = getPDO();
$stmt = $pdo->prepare("
    SELECT
        s.*,
        t.name,
        t.logo_url
    FROM standings s
    JOIN teams t ON s.team_id = t.id
    WHERE s.poule_id = ?
    ORDER BY s.points DESC, s.goal_diff DESC, s.goals_for DESC
");
$stmt->execute([$poule_id]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

json_ok($rows);
