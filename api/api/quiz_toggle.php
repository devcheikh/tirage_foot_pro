<?php
// backend/api/quiz_toggle.php
require_once __DIR__ . '/../auth/check_auth.php';
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/response.php';

$input = json_decode(file_get_contents('php://input'), true);

$id = intval($input['id'] ?? 0);
$is_active = !empty($input['is_active']) ? 1 : 0;

if ($id <= 0) {
    json_error("ID invalide.");
}

try {
    $pdo = getPDO();
    $stmt = $pdo->prepare("UPDATE quiz_questions SET is_active = ? WHERE id = ?");
    $stmt->execute([$is_active, $id]);
    
    json_ok(null, "Statut mis à jour avec succès.");
} catch (Exception $e) {
    json_error($e->getMessage());
}
