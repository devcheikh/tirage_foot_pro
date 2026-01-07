<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../core/db.php';

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;

if (!$id) {
    echo json_encode(["success" => false, "message" => "ID du créneau manquant."]);
    exit;
}

try {
    $pdo = getPDO();
    $stmt = $pdo->prepare("DELETE FROM stadium_slots WHERE id = ?");
    $stmt->execute([$id]);

    echo json_encode(["success" => true, "message" => "Créneau supprimé."]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
