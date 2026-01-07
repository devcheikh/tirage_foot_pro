<?php
// backend/api/setup_league_logo.php
header('Content-Type: application/json');
require_once __DIR__ . '/../core/db.php';

try {
    $pdo = getPDO();

    // Add league_logo_url to categories table
    $pdo->exec("ALTER TABLE categories ADD COLUMN IF NOT EXISTS league_logo_url VARCHAR(255) DEFAULT NULL");

    echo json_encode(["success" => true, "message" => "Table categories mise à jour avec le champ league_logo_url."]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
