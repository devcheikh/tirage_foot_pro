<?php
// backend/api/match_delete.php
require_once __DIR__ . '/../auth/check_auth.php';
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/response.php';

$input = json_decode(file_get_contents('php://input'), true);
$id = intval($input['id'] ?? 0);

if ($id <= 0) {
    json_error("ID du match manquant.");
}

try {
    $pdo = getPDO();
    $stmt = $pdo->prepare("DELETE FROM matches WHERE id = ?");
    $stmt->execute([$id]);

    json_ok(null, "Match supprimé avec succès.");
} catch (Exception $e) {
    json_error($e->getMessage());
}
