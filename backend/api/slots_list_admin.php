<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../core/db.php';

$stadium_id = $_GET['stadium_id'] ?? null;

if (!$stadium_id) {
    echo json_encode(["success" => false, "message" => "ID du stade manquant."]);
    exit;
}

try {
    $pdo = getPDO();
    $stmt = $pdo->prepare("SELECT * FROM stadium_slots WHERE stadium_id = ? ORDER BY start_time ASC");
    $stmt->execute([$stadium_id]);
    $slots = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "data" => $slots]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
