<?php
// backend/api/extra_delete.php
require_once __DIR__ . '/../auth/check_auth.php';
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/response.php';

$input = json_decode(file_get_contents('php://input'), true);
$type = $input['type'] ?? '';
$id = intval($input['id'] ?? 0);

if (!$type || !$id) json_error("Paramètres manquants.");

$pdo = getPDO();
$table = "";
if ($type === 'palmares') $table = "palmares";
else if ($type === 'sponsor') $table = "sponsors";
else if ($type === 'location') $table = "locations";
else json_error("Type invalide.");

try {
    $stmt = $pdo->prepare("DELETE FROM $table WHERE id = ?");
    $stmt->execute([$id]);
    json_ok(null, "Supprimé.");
} catch (Exception $e) {
    json_error($e->getMessage());
}
