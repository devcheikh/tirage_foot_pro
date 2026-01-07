<?php
// backend/api/teams_list.php

require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/response.php';

$category_id = intval($_GET['category_id'] ?? 0);

$pdo = getPDO();

if ($category_id > 0) {
    $stmt = $pdo->prepare("
        SELECT id, name, city, logo_url, is_seeded, created_at, category_id
        FROM teams
        WHERE category_id = ?
        ORDER BY name ASC
    ");
    $stmt->execute([$category_id]);
} else {
    // Récupérer TOUTES les équipes (pour la gestion globale des joueurs par ex)
    $stmt = $pdo->prepare("
        SELECT t.id, t.name, t.city, t.logo_url, t.is_seeded, t.created_at, c.name as category_name, c.season
        FROM teams t
        LEFT JOIN categories c ON t.category_id = c.id
        ORDER BY t.name ASC
    ");
    $stmt->execute();
}

$teams = $stmt->fetchAll();

json_ok($teams, 'Liste des équipes de la catégorie');
