<?php
// backend/api/setup_advanced.php
header('Content-Type: application/json');
require_once __DIR__ . '/../core/db.php';

try {
    $pdo = getPDO();

    // 1. Ensure products has stock column (it already has from previous setup_boutique_res.php)
    // But let's verify or add just in case.
    
    // 2. New Table: stadium_slots (To define fixed availability blocks)
    // E.g. Stadium 1 is available 08:00-10:00, 10:00-12:00, etc.
    $pdo->exec("CREATE TABLE IF NOT EXISTS stadium_slots (
        id INT AUTO_INCREMENT PRIMARY KEY,
        stadium_id INT NOT NULL,
        day_of_week TINYINT DEFAULT 0, -- 0=Everyday, 1=Mon, etc. (Not strictly needed if we just use fixed)
        start_time TIME NOT NULL,
        end_time TIME NOT NULL,
        price DECIMAL(10,2) DEFAULT 0,
        is_active BOOLEAN DEFAULT TRUE,
        FOREIGN KEY (stadium_id) REFERENCES locations(id) ON DELETE CASCADE
    )");

    // 3. Populate dummy slots for existing stadiums if empty
    $stCount = $pdo->query("SELECT COUNT(*) FROM stadium_slots")->fetchColumn();
    if ($stCount == 0) {
        $stadiums = $pdo->query("SELECT id FROM locations")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($stadiums as $sid) {
            $slots = [
                ['08:00', '10:00', 15000],
                ['10:00', '12:00', 15000],
                ['14:00', '16:00', 20000],
                ['16:00', '18:00', 25000],
                ['18:00', '20:00', 30000],
                ['20:00', '22:00', 30000],
            ];
            foreach ($slots as $sl) {
                $pdo->prepare("INSERT INTO stadium_slots (stadium_id, start_time, end_time, price) VALUES (?, ?, ?, ?)")
                    ->execute([$sid, $sl[0], $sl[1], $sl[2]]);
            }
        }
    }

    echo json_encode(["success" => true, "message" => "Base de données mise à jour vers la version avancée."]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
