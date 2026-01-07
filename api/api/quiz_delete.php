<?php
// backend/api/quiz_delete.php
require_once __DIR__ . '/../auth/check_auth.php';
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/response.php';

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    json_error("ID invalide.");
}

try {
    $pdo = getPDO();
    $stmt = $pdo->prepare("DELETE FROM quiz_questions WHERE id = ?");
    $stmt->execute([$id]);
    
    json_ok(null, "Question supprimée avec succès.");
} catch (Exception $e) {
    json_error($e->getMessage());
}
