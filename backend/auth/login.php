<?php
session_start();
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/response.php';

$input = json_decode(file_get_contents("php://input"), true);
$username = trim($input["username"] ?? "");
$password = $input["password"] ?? "";

$pdo = getPDO();
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($password, $user['password_hash'])) {
    json_error("Identifiants invalides", 401);
}

$_SESSION["user_id"] = $user["id"];
$_SESSION["username"] = $user["username"];

json_ok(["username"=>$user["username"]], "Connexion réussie");
