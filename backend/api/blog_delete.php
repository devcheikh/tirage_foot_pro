<?php
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/response.php';

$input = json_decode(file_get_contents('php://input'), true);
$id = $input['id'] ?? null;

if (!$id) {
    json_error('ID manquant');
}

try {
    $pdo = getPDO();
    $stmt = $pdo->prepare("DELETE FROM blog_posts WHERE id = ?");
    $stmt->execute([$id]);

    json_ok([], 'Article supprimé avec succès');
} catch (PDOException $e) {
    json_error($e->getMessage());
}
?>
