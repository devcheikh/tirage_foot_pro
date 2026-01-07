<?php
// backend/api/reservations_list.php
require_once __DIR__ . '/../auth/check_auth.php';
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/response.php';

$pdo = getPDO();
// Join with locations to get stadium name if stored there, otherwise use stadium_id as a name placeholder or assume a stadiums table exists
// For now, let's assume stadium_id refers to locations.id
$stmt = $pdo->query("SELECT r.*, l.name as stadium_name 
                    FROM reservations r 
                    LEFT JOIN locations l ON r.stadium_id = l.id 
                    ORDER BY r.reservation_date DESC, r.start_time DESC");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
json_ok($rows);
