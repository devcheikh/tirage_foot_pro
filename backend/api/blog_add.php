<?php
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/response.php';

$input = json_decode(file_get_contents('php://input'), true);
$title = $input['title'] ?? '';
$content = $input['content'] ?? '';
$image_url = $input['image_url'] ?? '';

if (empty($title) || empty($content)) {
    json_error('Titre et contenu requis');
}

try {
    $pdo = getPDO();
    $stmt = $pdo->prepare("INSERT INTO blog_posts (title, content, image_url) VALUES (?, ?, ?)");
    $stmt->execute([$title, $content, $image_url]);

    json_ok([], 'Article ajouté avec succès');
} catch (PDOException $e) {
    json_error($e->getMessage());
}
?>
