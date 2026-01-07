<?php
// backend/api/match_list.php
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/response.php';

$pdo = getPDO();

$poule_id    = intval($_GET['poule_id'] ?? 0);
$draw_id     = intval($_GET['draw_id'] ?? 0);
$category_id = intval($_GET['category_id'] ?? 0);
$status      = trim($_GET['status'] ?? ''); 
$match_date  = trim($_GET['match_date'] ?? '');

$where = [];
$params = [];

if ($poule_id > 0) {
    $where[] = "m.poule_id = ?";
    $params[] = $poule_id;
}
if ($draw_id > 0) {
    $where[] = "m.draw_id = ?";
    $params[] = $draw_id;
}
if ($category_id > 0) {
    $where[] = "d.category_id = ?";
    $params[] = $category_id;
}
if ($status !== '') {
    $where[] = "m.status = ?";
    $params[] = $status;
}
if ($match_date !== '') {
    $where[] = "m.match_date = ?";
    $params[] = $match_date;
}

$sql = "
    SELECT
        m.*,
        th.name  AS home_name,
        th.logo_url AS home_logo,
        ta.name  AS away_name,
        ta.logo_url AS away_logo,
        p.name   AS poule_name,
        d.label  AS draw_label
    FROM matches m
    JOIN teams th ON m.team_home = th.id
    JOIN teams ta ON m.team_away = ta.id
    JOIN poules p ON m.poule_id = p.id
    JOIN draws d  ON m.draw_id = d.id
";

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY m.match_date, m.match_time, m.id";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$matches = $stmt->fetchAll(PDO::FETCH_ASSOC);

json_ok($matches);
