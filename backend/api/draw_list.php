<?php
// backend/api/draw_list.php

require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/response.php';

$category_id = intval($_GET['category_id'] ?? 0);

$pdo = getPDO();

$sql = "
    SELECT d.id, d.label, d.nb_poules, d.created_at,
           c.name AS category_name, c.season
    FROM draws d
    JOIN categories c ON c.id = d.category_id
";
$params = [];

if ($category_id > 0) {
    $sql .= " WHERE d.category_id = ?";
    $params[] = $category_id;
}

$sql .= " ORDER BY d.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$draws = $stmt->fetchAll();

json_ok($draws, 'Liste des tirages');
