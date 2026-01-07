<?php
// backend/api/reservation_slots.php
header('Content-Type: application/json');
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/response.php';

$stadium_id = $_GET['stadium_id'] ?? 0;
$date = $_GET['date'] ?? date('Y-m-d');

if (!$stadium_id) json_error("ID Stade manquant.");

$pdo = getPDO();

// 1. Get all base slots for this stadium
$stmt = $pdo->prepare("SELECT * FROM stadium_slots WHERE stadium_id = ? AND is_active = 1 ORDER BY start_time ASC");
$stmt->execute([$stadium_id]);
$allSlots = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. Get existing (confirmed/pending) reservations for this date
$stmt = $pdo->prepare("SELECT start_time, end_time FROM reservations WHERE stadium_id = ? AND reservation_date = ? AND status != 'cancelled'");
$stmt->execute([$stadium_id, $date]);
$reserved = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Map reserved to simple array for easy check
$reservedTimes = array_map(function($r) {
    return $r['start_time'];
}, $reserved);

// 3. Mark as available or taken
$result = [];
foreach ($allSlots as $s) {
    $s['is_available'] = !in_array($s['start_time'], $reservedTimes);
    $result[] = $s;
}

json_ok($result);
