<?php
// backend/api/player_stats_top.php
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/response.php';

$category_id = intval($_GET['category_id'] ?? 0); // optionnel

$pdo = getPDO();

$sql = "
    SELECT 
        pl.id AS player_id,
        pl.name AS player_name,
        pl.position,
        pl.number,
        t.name AS team_name,
        t.logo_url,
        SUM(ps.goals) AS total_goals,
        SUM(ps.assists) AS total_assists
    FROM player_stats ps
    JOIN players pl ON ps.player_id = pl.id
    JOIN teams t ON pl.team_id = t.id
    JOIN matches m ON ps.match_id = m.id
    JOIN draws d ON m.draw_id = d.id
";

$params = [];
if ($category_id > 0) {
    $sql .= " WHERE d.category_id = ? ";
    $params[] = $category_id;
}

$sql .= "
    GROUP BY pl.id
    HAVING total_goals > 0
    ORDER BY total_goals DESC, total_assists DESC, player_name ASC
    LIMIT 50
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

json_ok($rows);
