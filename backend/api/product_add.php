<?php
// backend/api/product_add.php
require_once __DIR__ . '/../auth/check_auth.php';
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/response.php';

$input = json_decode(file_get_contents('php://input'), true);
$name = $input['name'] ?? '';
$price = floatval($input['price'] ?? 0);
$image_url = $input['image_url'] ?? '';
$stock = intval($input['stock'] ?? 0);
$desc = $input['description'] ?? '';

if (!$name || $price <= 0) json_error("Nom et prix requis.");

$pdo = getPDO();
$stmt = $pdo->prepare("INSERT INTO products (name, description, price, image_url, stock) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$name, $desc, $price, $image_url, $stock]);

json_ok(null, "Produit ajouté.");
