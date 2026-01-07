<?php
require_once __DIR__ . '/../core/db.php';
$pdo = getPDO();

$tables = ['categories', 'teams', 'products', 'locations', 'orders', 'reservations'];
$results = [];

foreach ($tables as $t) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM $t");
        $results[$t] = $stmt->fetchColumn();
    } catch (Exception $e) {
        $results[$t] = "Error: " . $e->getMessage();
    }
}

echo json_encode($results);
