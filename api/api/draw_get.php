<?php
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/response.php';

$draw_id = intval($_GET['draw_id'] ?? 0);
if ($draw_id <= 0) {
    json_error("ID tirage invalide.");
}

$pdo = getPDO();

// Infos tirage
$stmt = $pdo->prepare("SELECT d.*, c.name AS category_name, c.season
                       FROM draws d
                       JOIN categories c ON c.id = d.category_id
                       WHERE d.id = ?");
$stmt->execute([$draw_id]);
$draw = $stmt->fetch();

if (!$draw) {
    json_error("Tirage introuvable.", 404);
}

// Poules + équipes
$stmtPoules = $pdo->prepare("SELECT * FROM poules WHERE draw_id = ? ORDER BY rank ASC");
$stmtPoules->execute([$draw_id]);
$poules = $stmtPoules->fetchAll();

$result = [
    'draw'   => $draw,
    'poules' => []
];

$stmtTeams = $pdo->prepare("
    SELECT dt.position_in_poule, t.*
    FROM draw_teams dt
    JOIN teams t ON t.id = dt.team_id
    WHERE dt.poule_id = ?
    ORDER BY dt.position_in_poule ASC
");

foreach ($poules as $p) {
    $stmtTeams->execute([$p['id']]);
    $teams = $stmtTeams->fetchAll();
    $result['poules'][] = [
        'id'    => $p['id'],
        'name'  => $p['name'],
        'rank'  => $p['rank'],
        'teams' => $teams
    ];
}

json_ok($result, "Tirage trouvé.");
