<?php
require_once "../core/db.php";
require_once "../core/response.php";
require_once "../auth/check_auth.php";

$id = intval($_GET["id"] ?? 0);

if ($id <= 0) {
    json_error("ID invalide.");
}

$pdo = getPDO();
$stmt = $pdo->prepare("DELETE FROM teams WHERE id = ?");
$stmt->execute([$id]);

json_ok([], "Équipe supprimée.");
