<?php
// backend/auth/me.php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../core/response.php';

if (!isset($_SESSION['user_id'])) {
    json_error("Non connecté", 401);
}

json_ok([
    'user_id' => $_SESSION['user_id'],
    'username' => $_SESSION['username'] ?? 'Admin'
]);
