<?php
require_once "../auth/check_auth.php";
require_once "../core/response.php";

$dir = "../../uploads/logos/";
$baseUrl = "/tirage_foot_pro/uploads/logos/";

if (!is_dir($dir)) {
    json_ok([], "Aucun logo trouvé.");
}

$files = scandir($dir);
$logos = [];

$allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

foreach ($files as $file) {
    if ($file === '.' || $file === '..') continue;

    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    
    if (in_array($ext, $allowedExts)) {
        $logos[] = [
            "filename" => $file,
            "url" => $baseUrl . $file
        ];
    }
}

// Trier par date de modification (récent en premier) ? 
// Pour l'instant on laisse l'ordre par défaut ou on pourrait trier.
// Usort pour trier par date de modif si besoin.

json_ok($logos, count($logos) . " logos trouvés.");
