<?php
// backend/api/extra_add.php
require_once __DIR__ . '/../auth/check_auth.php';
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/response.php';

$input = json_decode(file_get_contents('php://input'), true);
$type = $input['type'] ?? ''; // 'palmares', 'sponsor', 'location'

if (!$type) json_error("Type manquant.");

$pdo = getPDO();

try {
    if ($type === 'palmares') {
        $stmt = $pdo->prepare("INSERT INTO palmares (year, team_name, category, logo_url) VALUES (?, ?, ?, ?)");
        $stmt->execute([$input['year'], $input['team_name'], $input['category'], $input['logo_url']]);
    } else if ($type === 'sponsor') {
        $stmt = $pdo->prepare("INSERT INTO sponsors (name, logo_url, link_url) VALUES (?, ?, ?)");
        $stmt->execute([$input['name'], $input['logo_url'], $input['link_url']]);
    } else if ($type === 'location') {
        $stmt = $pdo->prepare("INSERT INTO locations (name, address, map_url) VALUES (?, ?, ?)");
        $stmt->execute([$input['name'], $input['address'], $input['map_url']]);
    } else {
        json_error("Type invalide.");
    }
    json_ok(null, "Élément ajouté.");
} catch (Exception $e) {
    json_error($e->getMessage());
}
