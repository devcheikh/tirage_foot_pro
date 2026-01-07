<?php
// backend/api/quiz_add.php
require_once __DIR__ . '/../auth/check_auth.php';
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/response.php';

$input = json_decode(file_get_contents('php://input'), true);

$question = trim($input['question'] ?? '');
$option_a = trim($input['option_a'] ?? '');
$option_b = trim($input['option_b'] ?? '');
$option_c = trim($input['option_c'] ?? '');
$option_d = trim($input['option_d'] ?? '');
$correct_option = intval($input['correct_option'] ?? 0);
$difficulty = trim($input['difficulty'] ?? 'moyen');
$category = trim($input['category'] ?? 'Général');

if (empty($question) || empty($option_a) || empty($option_b) || empty($option_c) || empty($option_d)) {
    json_error("Tous les champs sont obligatoires.");
}

if ($correct_option < 0 || $correct_option > 3) {
    json_error("L'option correcte doit être entre 0 et 3.");
}

try {
    $pdo = getPDO();
    $stmt = $pdo->prepare("
        INSERT INTO quiz_questions (question, option_a, option_b, option_c, option_d, correct_option, difficulty, category)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$question, $option_a, $option_b, $option_c, $option_d, $correct_option, $difficulty, $category]);
    
    json_ok(['id' => $pdo->lastInsertId()], "Question ajoutée avec succès.");
} catch (Exception $e) {
    json_error($e->getMessage());
}
