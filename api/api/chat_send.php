<?php
header('Content-Type: application/json');
require_once '../config.php';

$input = json_decode(file_get_contents('php://input'), true);
$nickname = $input['nickname'] ?? 'Anonyme';
$message = $input['message'] ?? '';

if (empty($message)) {
    echo json_encode(['status' => 'error', 'message' => 'Message vide']);
    exit;
}

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("INSERT INTO chat_messages (nickname, message) VALUES (?, ?)");
    $stmt->execute([$nickname, $message]);

    echo json_encode(['status' => 'success']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
