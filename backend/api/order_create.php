<?php
// backend/api/order_create.php
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/response.php';

$input = json_decode(file_get_contents('php://input'), true);
$name = $input['customer_name'] ?? '';
$phone = $input['customer_phone'] ?? '';
$items = $input['items'] ?? [];
$total = floatval($input['total_price'] ?? 0);

if (!$name || !$phone || empty($items)) json_error("Données de commande incomplètes.");

$pdo = getPDO();
$pdo->beginTransaction();

try {
    $stmt = $pdo->prepare("INSERT INTO orders (customer_name, customer_phone, total_price, items_json) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $phone, $total, json_encode($items)]);

    // Decrement stock
    $updateStmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");
    foreach($items as $item) {
        $qty = $item['quantity'] ?? 1;
        $updateStmt->execute([$qty, $item['id'], $qty]);
    }

    $pdo->commit();
    json_ok(null, "Commande envoyée. Nous vous contacterons bientôt.");
} catch (Exception $e) {
    $pdo->rollBack();
    json_error("Erreur technique : " . $e->getMessage());
}
