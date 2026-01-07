<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../core/response.php';

if (!isset($_SESSION['user_id'])) {
    json_error("Non autorisé", 403);
}
