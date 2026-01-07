<?php
// backend/api/quiz_update.php
require_once __DIR__ . '/../auth/check_auth.php';
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/response.php';

$input = json_decode(file_get_contents('php://input'), true);

$id = intval($input['id'] ?? 0);
$question = trim($input['question'] ?? '');
$option_a = trim($input['option_a'] ?? '');
$option_b = trim($input['option_b'] ?? '');
$option_c = trim($input['option_c'] ?? '');
$option_d = trim($input['option_d'] ?? '');
$correct_option = intval($input['correct_option'] ?? 0);
$difficulty = trim($input['difficulty'] ?? 'moyen');
$category = trim($input['category'] ?? 'Général');

if ($id <= 0) {
    json_error("ID invalide.");
}

if (empty($question) || empty($option_a) || empty($option_b) || empty($option_c) || empty($option_d)) {
    json_error("Tous les champs sont obligatoires.");
}

try {
    $pdo = getPDO();
    $stmt = $pdo->prepare("
        UPDATE quiz_questions 
        SET question = ?, option_a = ?, option_b = ?, option_c = ?, option_d = ?, 
            correct_option = ?, difficulty = ?, category = ?
        WHERE id = ?
    ");
    $stmt->execute([$question, $option_a, $option_b, $option_c, $option_d, $correct_option, $difficulty, $category, $id]);
    
    json_ok(null, "Question mise à jour avec succès.");
} catch (Exception $e) {
    json_error($e->getMessage());
}
