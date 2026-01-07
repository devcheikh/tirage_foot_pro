<?php
require_once "../core/db.php";
require_once "../core/response.php";
require_once "../auth/check_auth.php";

$input = json_decode(file_get_contents("php://input"), true);

$category_id = intval($input["category_id"] ?? 0);
$name = trim($input["name"] ?? "");
$city = trim($input["city"] ?? "");
$logo = trim($input["logo_url"] ?? "");
$is_seeded = intval($input["is_seeded"] ?? 0);

if ($category_id <= 0 || $name === "") {
    json_error("Données invalides.");
}

$pdo = getPDO();
try {
    $stmt = $pdo->prepare("
        INSERT INTO teams (category_id, name, city, logo_url, is_seeded)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$category_id, $name, $city, $logo, $is_seeded]);
    json_ok([], "Équipe ajoutée.");
} catch (PDOException $e) {
    json_error("Erreur SQL : " . $e->getMessage());
}
