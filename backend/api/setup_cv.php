<?php
// backend/api/setup_cv.php
header('Content-Type: application/json');
require_once __DIR__ . '/../core/db.php';

try {
    $pdo = getPDO();

    // Add CV-related columns to players table
    $columns = [
        "is_visible" => "BOOLEAN DEFAULT FALSE",
        "bio" => "TEXT",
        "height" => "FLOAT",
        "weight" => "FLOAT",
        "preferred_foot" => "VARCHAR(20)",
        "skills" => "TEXT",
        "video_url" => "VARCHAR(255)",
        "license_number" => "VARCHAR(50) UNIQUE"
    ];

    foreach ($columns as $col => $type) {
        try {
            $pdo->exec("ALTER TABLE players ADD COLUMN $col $type");
        } catch (Exception $e) {
            // Column might already exist, ignore
        }
    }

    echo json_encode(["success" => true, "message" => "Table players mise à jour avec les champs CV."]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
