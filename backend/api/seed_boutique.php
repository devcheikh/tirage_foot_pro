<?php
// backend/api/seed_boutique.php
header('Content-Type: application/json');
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/response.php';

$pdo = getPDO();

$products = [
    [
        'name' => 'Maillot Officiel LPA 2025 (Home)',
        'description' => 'Maillot haut de gamme avec technologie respirante. Couleurs officielles.',
        'price' => 15000,
        'image_url' => 'assets/img/products/maillot_home.jpg',
        'stock' => 100
    ],
    [
        'name' => 'Maillot Officiel LPA 2025 (Away)',
        'description' => 'Design moderne épuré pour les matchs à l\'extérieur.',
        'price' => 15000,
        'image_url' => 'assets/img/products/maillot_away.jpg',
        'stock' => 80
    ],
    [
        'name' => 'Ballon Match Pro',
        'description' => 'Ballon certifié pour la compétition officielle.',
        'price' => 25000,
        'image_url' => 'assets/img/products/ballon_pro.jpg',
        'stock' => 30
    ],
    [
        'name' => 'Casquette Fan Edition',
        'description' => 'Affichez votre soutien avec style.',
        'price' => 7500,
        'image_url' => 'assets/img/products/casquette.jpg',
        'stock' => 50
    ]
];

try {
    $stmt = $pdo->prepare("INSERT INTO products (name, description, price, image_url, stock) VALUES (?, ?, ?, ?, ?)");
    foreach ($products as $p) {
        $stmt->execute([$p['name'], $p['description'], $p['price'], $p['image_url'], $p['stock']]);
    }
    echo json_encode(["success" => true, "message" => "Boutique actualisée avec des produits démo !"]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
