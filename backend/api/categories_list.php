<?php
// backend/api/categories_list.php

require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/response.php';

$pdo = getPDO();

// Liste simple des catégories
$stmt = $pdo->query("
    SELECT id, name, season, league_logo_url, created_at
    FROM categories
    ORDER BY season DESC, name ASC
");

$categories = $stmt->fetchAll();

json_ok($categories, 'Liste des catégories');
