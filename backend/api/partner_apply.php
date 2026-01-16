<?php
// backend/api/partner_apply.php
header('Content-Type: application/json');
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/response.php';

$input = json_decode(file_get_contents('php://input'), true);

$name = trim($input['name'] ?? '');
$league = trim($input['league'] ?? '');
$phone = trim($input['phone'] ?? '');
$message = trim($input['message'] ?? '');

if (empty($name) || empty($phone)) {
    json_error("Le nom et le téléphone sont obligatoires.");
}

try {
    $pdo = getPDO();
    
    // Insert into partnership table
    $stmt = $pdo->prepare("
        INSERT INTO partnership_requests (name, league_name, phone, message)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$name, $league, $phone, $message]);
    
    // Also create a notification for the admin
    $notif = "Nouveau partenaire potentiel : $name ($league). Tel: $phone. Message: $message";
    $stmtNotif = $pdo->prepare("
        INSERT INTO system_notifications (type, target_id, target_role, message)
        VALUES ('partner_new', 0, 'admin', ?)
    ");
    $stmtNotif->execute([$notif]);
    
    json_ok(null, "Votre demande a été envoyée avec succès. Nous vous contacterons bientôt.");
} catch (Exception $e) {
    json_error($e->getMessage());
}
