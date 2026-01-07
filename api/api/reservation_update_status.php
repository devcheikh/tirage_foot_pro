<?php
// backend/api/reservation_update_status.php
require_once __DIR__ . '/../auth/check_auth.php';
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/response.php';

$input = json_decode(file_get_contents('php://input'), true);
$id = intval($input['id'] ?? 0);
$status = $input['status'] ?? '';

if (!$id || !$status) json_error("ID et statut requis.");

$allowed = ['pending', 'confirmed', 'cancelled'];
if (!in_array($status, $allowed)) json_error("Statut invalide.");

$pdo = getPDO();

// Get customer info for notification
$stmtInfo = $pdo->prepare("SELECT customer_name, customer_phone, reservation_date, start_time FROM reservations WHERE id = ?");
$stmtInfo->execute([$id]);
$resInfo = $stmtInfo->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("UPDATE reservations SET status = ? WHERE id = ?");
$stmt->execute([$status, $id]);

// Notification Customer
if ($resInfo) {
    $readableStatus = ($status === 'confirmed') ? 'CONFIRMÉE' : (($status === 'cancelled') ? 'REFUSÉE' : 'MISE EN ATTENTE');
    $msg = "Bonjour {$resInfo['customer_name']}, votre réservation pour le {$resInfo['reservation_date']} à {$resInfo['start_time']} est désormais $readableStatus.";
    $pdo->prepare("INSERT INTO system_notifications (type, target_id, target_role, customer_phone, message) VALUES ('reservation_status', ?, 'customer', ?, ?)")
        ->execute([$id, $resInfo['customer_phone'], $msg]);
}

json_ok(null, "Réservation mise à jour.");
