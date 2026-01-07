<?php
// backend/api/order_update_status.php
require_once __DIR__ . '/../auth/check_auth.php';
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/response.php';

$input = json_decode(file_get_contents('php://input'), true);
$id = intval($input['id'] ?? 0);
$status = $input['status'] ?? '';

if (!$id || !$status) json_error("ID et statut requis.");

$allowed = ['pending', 'confirmed', 'shipped', 'cancelled'];
if (!in_array($status, $allowed)) json_error("Statut invalide.");

$pdo = getPDO();
$stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
$stmt->execute([$status, $id]);

json_ok(null, "Statut de commande mis à jour.");
