<?php
// backend/api/locations_list.php
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/response.php';

$pdo = getPDO();
$stmt = $pdo->query("SELECT * FROM locations ORDER BY name ASC");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
json_ok($rows);
