<?php
// backend/api/cv_apply.php
header('Content-Type: application/json');
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/response.php';

$input = json_decode(file_get_contents('php://input'), true);

$name = trim($input['name'] ?? '');
$phone = trim($input['phone'] ?? '');
$position = trim($input['position'] ?? '');
$bio = trim($input['bio'] ?? '');

if (empty($name) || empty($phone)) {
    json_error("Le nom et le téléphone sont obligatoires.");
}

try {
    $pdo = getPDO();
    
    // We could store this in a 'cv_applications' table,
    // but for now let's just create a notification for the admin.
    
    $message = "Nouvelle demande de CV : $name ($phone) - Poste: $position. Bio: $bio";
    
    $stmt = $pdo->prepare("
        INSERT INTO system_notifications (type, target_id, target_role, message)
        VALUES ('cv_new', 0, 'admin', ?)
    ");
    $stmt->execute([$message]);
    
    json_ok(null, "Candidature envoyée avec succès.");
} catch (Exception $e) {
    json_error($e->getMessage());
}
