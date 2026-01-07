<?php
// backend/api/reservation_create.php
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/response.php';

$input = json_decode(file_get_contents('php://input'), true);
$stadium_id = intval($input['stadium_id'] ?? 0);
$name = $input['customer_name'] ?? '';
$phone = $input['customer_phone'] ?? '';
$date = $input['reservation_date'] ?? '';
$start = $input['start_time'] ?? '';
$end = $input['end_time'] ?? '';
$notes = $input['notes'] ?? '';

if (!$stadium_id || !$name || !$phone || !$date || !$start || !$end) {
    json_error("Tous les champs obligatoires doivent être remplis.");
}

$pdo = getPDO();

// Double check availability on server side
$stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE stadium_id = ? AND reservation_date = ? AND start_time = ? AND status != 'cancelled'");
$stmtCheck->execute([$stadium_id, $date, $start]);
if ($stmtCheck->fetchColumn() > 0) {
    json_error("Désolé, ce créneau vient tout juste d'être réservé par quelqu'un d'autre.");
}

$stmt = $pdo->prepare("INSERT INTO reservations (stadium_id, customer_name, customer_phone, reservation_date, start_time, end_time, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->execute([$stadium_id, $name, $phone, $date, $start, $end, $notes]);
$resId = $pdo->lastInsertId();

// Notification Admin
$msg = "Nouvelle demande de réservation de $name ($phone) pour le $date à $start.";
$pdo->prepare("INSERT INTO system_notifications (type, target_id, target_role, message) VALUES ('reservation_new', ?, 'admin', ?)")
    ->execute([$resId, $msg]);

json_ok(null, "Demande de réservation envoyée. Nous reviendrons vers vous pour confirmation.");
