<?php
// backend/api/setup_player_extras.php
header('Content-Type: application/json');
require_once __DIR__ . '/../core/db.php';

try {
    $pdo = getPDO();

    // Add new columns to players table
    $columns = [
        "ALTER TABLE players ADD COLUMN IF NOT EXISTS matches_played INT DEFAULT 0",
        "ALTER TABLE players ADD COLUMN IF NOT EXISTS goals INT DEFAULT 0",
        "ALTER TABLE players ADD COLUMN IF NOT EXISTS assists INT DEFAULT 0",
        "ALTER TABLE players ADD COLUMN IF NOT EXISTS instagram VARCHAR(100) DEFAULT NULL",
        "ALTER TABLE players ADD COLUMN IF NOT EXISTS twitter VARCHAR(100) DEFAULT NULL",
        "ALTER TABLE players ADD COLUMN IF NOT EXISTS linkedin VARCHAR(100) DEFAULT NULL",
        "ALTER TABLE players ADD COLUMN IF NOT EXISTS motto TEXT DEFAULT NULL"
    ];

    foreach ($columns as $sql) {
        try {
            $pdo->exec($sql);
        } catch (PDOException $e) {
            // Column might already exist, continue
            if (strpos($e->getMessage(), 'Duplicate column name') === false) {
                throw $e;
            }
        }
    }

    echo json_encode([
        "success" => true, 
        "message" => "Colonnes ajoutées avec succès : matches_played, goals, assists, instagram, twitter, linkedin, motto"
    ]);
} catch (Exception $e) {
    echo json_encode([
        "success" => false, 
        "message" => "Erreur : " . $e->getMessage()
    ]);
}
