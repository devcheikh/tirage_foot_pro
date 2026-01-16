<?php
// Temporary script to check players table
require_once __DIR__ . '/core/db.php';

try {
    $pdo = getPDO();

    // Check if players table exists
    $tables = $pdo->query("SHOW TABLES LIKE 'players'")->fetchAll();
    if (empty($tables)) {
        echo "Table 'players' does not exist.\n";
        exit;
    }

    // Count total players
    $count = $pdo->query("SELECT COUNT(*) FROM players")->fetchColumn();
    echo "Total players in database: $count\n";

    // Count visible players
    $visibleCount = $pdo->query("SELECT COUNT(*) FROM players WHERE is_visible = 1")->fetchColumn();
    echo "Visible players: $visibleCount\n";

    // Show first 5 players
    $stmt = $pdo->query("SELECT id, name, team_id, position, is_visible FROM players LIMIT 5");
    $players = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "\nFirst 5 players:\n";
    foreach ($players as $player) {
        echo "- ID: {$player['id']}, Name: {$player['name']}, Team: {$player['team_id']}, Position: {$player['position']}, Visible: " . ($player['is_visible'] ? 'Yes' : 'No') . "\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>