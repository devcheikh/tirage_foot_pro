<?php
// backend/api/seed_locations.php
header('Content-Type: application/json');
require_once __DIR__ . '/../core/db.php';
$pdo = getPDO();

$stadiums = [
    ['name' => 'Stade Municipal de Dakar', 'address' => 'Dakar, Sénégal'],
    ['name' => 'Terrain Annexe LPA', 'address' => 'Zone Sportive 1'],
    ['name' => 'Stadium Iba Mar Diop', 'address' => 'Medina, Dakar'],
    ['name' => 'Stade Lat Dior', 'address' => 'Thiès, Sénégal']
];

try {
    $stmt = $pdo->prepare("INSERT INTO locations (name, address) VALUES (?, ?)");
    foreach ($stadiums as $s) {
        $stmt->execute([$s['name'], $s['address']]);
    }
    echo json_encode(["success" => true, "message" => "Terrains ajoutés !"]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
