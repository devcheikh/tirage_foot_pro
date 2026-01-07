<?php
// backend/api/setup_notifications.php
header('Content-Type: application/json');
require_once __DIR__ . '/../core/db.php';

try {
    $pdo = getPDO();

    $pdo->exec("CREATE TABLE IF NOT EXISTS system_notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        type ENUM('reservation_new', 'reservation_status', 'order_new', 'order_status', 'cv_new') NOT NULL,
        target_id INT NOT NULL, -- reservation_id or order_id
        target_role ENUM('admin', 'customer') NOT NULL,
        customer_phone VARCHAR(50), -- To identify customer if role is customer
        message TEXT NOT NULL,
        is_read BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    echo json_encode(["success" => true, "message" => "Table des notifications créée."]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
