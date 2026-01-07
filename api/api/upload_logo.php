<?php
require_once "../auth/check_auth.php";
require_once "../core/response.php";

$targetDir = "../../uploads/logos/";

// Créer le dossier s'il n'existe pas
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0755, true);
}

if (!isset($_FILES["logo"])) {
    json_error("Aucun fichier reçu.");
}

$file = $_FILES["logo"];

// 1. Vérifier erreurs upload
if ($file["error"] !== 0) {
    json_error("Erreur upload code : " . $file["error"]);
}

// 2. Vérifier la taille (Max 2 Mo)
$maxSize = 2 * 1024 * 1024; // 2 Mo
if ($file["size"] > $maxSize) {
    json_error("Le fichier est trop volumineux (Max 2 Mo).");
}

// 3. Vérifier le type MIME réel
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($file["tmp_name"]);

$allowedMimes = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/gif'  => 'gif',
    'image/webp' => 'webp',
];

if (!array_key_exists($mimeType, $allowedMimes)) {
    json_error("Type de fichier invalide ($mimeType). Seuls JPG, PNG, GIF et WEBP sont autorisés.");
}

// 4. Générer l'extension et le nom final
$ext = $allowedMimes[$mimeType];
$filename = "logo_" . time() . "_" . bin2hex(random_bytes(4)) . "." . $ext;
$targetPath = $targetDir . $filename;

// 5. Déplacer le fichier
if (!move_uploaded_file($file["tmp_name"], $targetPath)) {
    json_error("Impossible de sauvegarder le fichier sur le serveur.");
}

// URL accessible publiquement
$url = "/tirage_foot_pro/uploads/logos/" . $filename;

json_ok(["url" => $url], "Logo uploadé avec succès !");
