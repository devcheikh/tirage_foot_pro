<?php
require_once "../core/db.php";
require_once "../core/response.php";
require_once "../auth/check_auth.php";

$id = intval($_REQUEST["id"] ?? 0);

if ($id <= 0) {
    json_error("ID invalide.");
}

$pdo = getPDO();

// 1. Vérifier si des équipes sont liées
$stmt = $pdo->prepare("SELECT COUNT(*) FROM teams WHERE category_id = ?");
$stmt->execute([$id]);
$count = $stmt->fetchColumn();

if ($count > 0) {
    json_error("Impossible de supprimer : cette catégorie contient $count équipes. Veuillez d'abord supprimer ou déplacer ces équipes.");
}

// 2. Supprimer la catégorie
$stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
if ($stmt->execute([$id])) {
    json_ok([], "Catégorie supprimée.");
} else {
    json_error("Erreur lors de la suppression.");
}
