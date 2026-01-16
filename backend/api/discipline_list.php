<?php
// backend/api/discipline_list.php
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/response.php';

$category_id = intval($_GET['category_id'] ?? 0);

$pdo = getPDO();

$sql = "
    SELECT 
        t.id AS team_id,
        t.name AS team_name,
        t.logo_url,
        SUM(ps.yellow_cards) AS total_yellow,
        SUM(ps.red_cards) AS total_red,
        (SUM(ps.yellow_cards) + (SUM(ps.red_cards) * 3)) AS penalty_points
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
    GROUP BY t.id
    ORDER BY penalty_points ASC, team_name ASC
    LIMIT 20
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

json_ok($rows);
