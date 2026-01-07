<?php
header('Content-Type: application/json');
require_once '../config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. S'assurer que la table existe
    $pdo->exec("CREATE TABLE IF NOT EXISTS blog_posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        image_url VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 2. Vider pour le test (optionnel)
    $pdo->exec("DELETE FROM blog_posts");

    // 3. Ajouter des articles de démo
    $posts = [
        [
            "Lancement de la Ligue Pro !",
            "Bienvenue sur la plateforme officielle de la Ligue Pro Des Academies. Suivez tous les résultats en direct !",
            "https://images.unsplash.com/photo-1574629810360-7efbbe195018?auto=format&fit=crop&w=800&q=80"
        ],
        [
            "Top 5 des Académies",
            "Découvrez notre analyse des 5 académies les plus performantes de cette saison. Qui prendra la tête ?",
            "https://images.unsplash.com/photo-1526232759535-7ad0960eafe0?auto=format&fit=crop&w=800&q=80"
        ],
        [
            "Nouveauté : Le Chat Live",
            "Vous pouvez désormais discuter en direct avec les autres supporters via notre nouvelle bulle de chat !",
            "https://images.unsplash.com/photo-1510511459019-5dda7724fd87?auto=format&fit=crop&w=800&q=80"
        ]
    ];

    $stmt = $pdo->prepare("INSERT INTO blog_posts (title, content, image_url) VALUES (?, ?, ?)");
    foreach ($posts as $post) {
        $stmt->execute($post);
    }

    echo json_encode(["status" => "success", "message" => "3 articles de blog ont été ajoutés !"]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
