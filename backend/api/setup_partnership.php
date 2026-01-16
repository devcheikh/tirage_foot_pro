<?php
// backend/api/setup_partnership.php
header('Content-Type: application/json');
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/response.php';

try {
    $pdo = getPDO();
    
    $sql = "
    CREATE TABLE IF NOT EXISTS partnership_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        league_name VARCHAR(255),
        phone VARCHAR(50),
        message TEXT,
        status ENUM('pending', 'contacted', 'accepted', 'rejected') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    
    $pdo->exec($sql);
    
    json_ok(null, "Table partnership_requests créée avec succès.");
} catch (Exception $e) {
    json_error($e->getMessage());
}
