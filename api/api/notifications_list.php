<?php
// backend/api/notifications_list.php
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/response.php';

$role = $_GET['role'] ?? 'admin';
$phone = $_GET['phone'] ?? '';

$pdo = getPDO();

if ($role === 'admin') {
    // Admin needs auth ideally, but for internal polling simplicity:
    $stmt = $pdo->prepare("SELECT * FROM system_notifications WHERE target_role = 'admin' AND is_read = 0 ORDER BY created_at DESC LIMIT 5");
    $stmt->execute();
} else {
    if (!$phone) json_error("Numéro de téléphone requis.");
    $stmt = $pdo->prepare("SELECT * FROM system_notifications WHERE target_role = 'customer' AND customer_phone = ? AND is_read = 0 ORDER BY created_at DESC");
    $stmt->execute([$phone]);
}

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
json_ok($rows);
