<?php
// backend/api/products_list.php
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/response.php';

$pdo = getPDO();
$stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
json_ok($rows);
