<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../core/db.php';

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;
$stadium_id = $data['stadium_id'] ?? null;
$start_time = $data['start_time'] ?? null;
$end_time = $data['end_time'] ?? null;
$price = $data['price'] ?? 0;

if (!$stadium_id || !$start_time || !$end_time) {
    echo json_encode(["success" => false, "message" => "Données manquantes (stade, début, fin)."]);
    exit;
}

try {
    $pdo = getPDO();
    if ($id) {
        // Update
        $stmt = $pdo->prepare("UPDATE stadium_slots SET start_time = ?, end_time = ?, price = ? WHERE id = ?");
        $stmt->execute([$start_time, $end_time, $price, $id]);
        $message = "Créneau mis à jour.";
    } else {
        // Create
        $stmt = $pdo->prepare("INSERT INTO stadium_slots (stadium_id, start_time, end_time, price) VALUES (?, ?, ?, ?)");
        $stmt->execute([$stadium_id, $start_time, $end_time, $price]);
        $message = "Créneau créé.";
    }

    echo json_encode(["success" => true, "message" => $message]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
