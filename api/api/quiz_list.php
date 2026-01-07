<?php
// backend/api/quiz_list.php
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/response.php';

$admin = isset($_GET['admin']) && $_GET['admin'] == '1';

// Si admin=1, on vérifie l'authentification
if ($admin) {
    require_once __DIR__ . '/../auth/check_auth.php';
}

$pdo = getPDO();

$sql = "SELECT * FROM quiz_questions";
if (!$admin) {
    $sql .= " WHERE is_active = 1";
}
$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->query($sql);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

json_ok($questions);
