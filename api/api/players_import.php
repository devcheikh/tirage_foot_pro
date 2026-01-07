<?php
// backend/api/players_import.php
require_once __DIR__ . '/../auth/check_auth.php';
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error("Méthode non autorisée.");
}

$team_id = intval($_POST['team_id'] ?? 0);
if ($team_id <= 0) {
    json_error("team_id est obligatoire.");
}

if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
    json_error("Erreur lors du téléchargement du fichier.");
}

$fileTmpPath = $_FILES['csv_file']['tmp_name'];
$fileName = $_FILES['csv_file']['name'];
$fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

if ($fileExtension !== 'csv') {
    json_error("Seuls les fichiers CSV sont acceptés.");
}

$pdo = getPDO();
$importedCount = 0;
$errors = [];

if (($handle = fopen($fileTmpPath, "r")) !== FALSE) {
    // Skip BOM if present
    $bom = fread($handle, 3);
    if ($bom != "\xEF\xBB\xBF") {
        rewind($handle);
    }
    
    // Detect delimiter
    $firstLine = fgets($handle);
    $delimiter = (strpos($firstLine, ';') !== false) ? ';' : ',';
    rewind($handle);
    if ($bom == "\xEF\xBB\xBF") {
       fread($handle, 3);
    }

    // Skip header
    fgetcsv($handle, 1000, $delimiter);

    $stmt = $pdo->prepare("
        INSERT INTO players (team_id, name, position, number, birthdate, license_number)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $lineNumber = 1;
    while (($data = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
        $lineNumber++;
        // Expected columns: Name, Position, Number, Birthdate (YYYY-MM-DD), License
        if (count($data) < 1) continue;

        $name = trim($data[0] ?? '');
        if (empty($name)) continue;

        $position = trim($data[1] ?? null);
        $number = intval($data[2] ?? 0);
        $birthdate = trim($data[3] ?? null);
        $license = trim($data[4] ?? null);

        // Basic validation for birthdate
        if ($birthdate && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthdate)) {
            $birthdate = null;
        }

        try {
            $stmt->execute([
                $team_id,
                $name,
                $position ?: null,
                $number ?: null,
                $birthdate ?: null,
                $license ?: null
            ]);
            $importedCount++;
        } catch (Exception $e) {
            $errors[] = "Ligne $lineNumber ($name): " . $e->getMessage();
        }
    }
    fclose($handle);
}

if ($importedCount > 0) {
    $msg = "$importedCount joueurs importés avec succès.";
    if (!empty($errors)) {
        $msg .= " Cependant, certaines erreurs sont survenues : " . implode(", ", $errors);
    }
    json_ok(['count' => $importedCount], $msg);
} else {
    json_error("Aucun joueur n'a pu être importé. Erreurs : " . implode(", ", $errors));
}
