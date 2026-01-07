<?php
require_once "../auth/check_auth.php";
require_once "../core/response.php";

$targetDir = "../../uploads/blog/";

// Créer le dossier s'il n'existe pas
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0755, true);
}

if (!isset($_FILES["image"])) {
    json_error("Aucune image reçue.");
}

$file = $_FILES["image"];

// 1. Vérifier erreurs upload
if ($file["error"] !== 0) {
    json_error("Erreur upload code : " . $file["error"]);
}

// 2. Vérifier la taille (Max 5 Mo pour le blog)
$maxSize = 5 * 1024 * 1024; 
if ($file["size"] > $maxSize) {
    json_error("L'image est trop volumineuse (Max 5 Mo).");
}

// 3. Vérifier le type MIME
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($file["tmp_name"]);

$allowedMimes = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/gif'  => 'gif',
    'image/webp' => 'webp',
];

if (!array_key_exists($mimeType, $allowedMimes)) {
    json_error("Format invalide. JPG, PNG, GIF ou WEBP uniquement.");
}

// 4. Nom du fichier
$ext = $allowedMimes[$mimeType];
$filename = "blog_" . time() . "_" . bin2hex(random_bytes(4)) . "." . $ext;
$targetPath = $targetDir . $filename;

// 5. Sauvegarder
if (!move_uploaded_file($file["tmp_name"], $targetPath)) {
    json_error("Erreur lors de la sauvegarde sur le serveur.");
}

// URL accessible
$url = "/tirage_foot_pro/uploads/blog/" . $filename;

json_ok(["url" => $url], "Image uploadée avec succès !");
?>
