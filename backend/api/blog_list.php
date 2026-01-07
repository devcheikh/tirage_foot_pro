<?php
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/response.php';

$pdo = getPDO();

// S'assurer que la table existe
$pdo->exec("CREATE TABLE IF NOT EXISTS blog_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$stmt = $pdo->query("SELECT * FROM blog_posts ORDER BY created_at DESC");
$posts = $stmt->fetchAll();

json_ok($posts, 'Articles du blog');
?>
