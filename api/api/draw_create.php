<?php
require_once __DIR__ . '/../auth/check_auth.php';
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/response.php';

$input = json_decode(file_get_contents('php://input'), true);

// Paramètres reçus du dashboard
$category_id = intval($input['category_id'] ?? 0);
$nb_poules   = intval($input['nb_poules'] ?? 0);
$type        = trim($input['type'] ?? 'groups'); // NEW : groups | league
$label       = trim($input['label'] ?? '');

if ($category_id <= 0) {
    json_error("Catégorie obligatoire.");
}

// Si CHAMPIONNAT : toujours 1 poule
if ($type === "league") {
    $nb_poules = 1;
}

if ($nb_poules <= 0) {
    json_error("Nombre de poules invalide.");
}

if ($label === '') {
    $label = ($type === "league")
        ? "Championnat catégorie $category_id"
        : "Tirage catégorie $category_id";
}

$pdo = getPDO();

/* ============================================================
   🔍 1) Récupérer toutes les équipes de la catégorie
   ============================================================ */
$stmt = $pdo->prepare("SELECT * FROM teams WHERE category_id = ? ORDER BY name");
$stmt->execute([$category_id]);
$teams = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($teams) < 1) {
    json_error("Aucune équipe trouvée dans cette catégorie.");
}

if ($type !== "league" && count($teams) < $nb_poules) {
    json_error("Il y a moins d'équipes que de poules.");
}

/* ============================================================
   📌 2) Si CHAMPIONNAT : on mélange et basta.
   ============================================================ */
if ($type === "league") {
    shuffle($teams);
}

/* ============================================================
   🏆 3) Tirage classique par têtes de série
   ============================================================ */
$seeded = [];
$others = [];

if ($type !== "league") {
    foreach ($teams as $t) {
        if (!empty($t['is_seeded'])) {
            $seeded[] = $t;
        } else {
            $others[] = $t;
        }
    }

    shuffle($seeded);
    shuffle($others);
}

/* ============================================================
   🏗 4) Création du tirage + poules
   ============================================================ */
$pdo->beginTransaction();

try {
    // Enregistrer le tirage
    $stmt = $pdo->prepare("
        INSERT INTO draws (category_id, label, nb_poules, type)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$category_id, $label, $nb_poules, $type]);
    $draw_id = $pdo->lastInsertId();

    // Création des poules
    $poules = [];

    for ($i = 0; $i < $nb_poules; $i++) {

        // NOM DES POULES
        $name =
            ($type === "league")
                ? "Championnat"
                : 'Poule ' . chr(65 + $i);

        $stmt = $pdo->prepare("
            INSERT INTO poules (draw_id, name, rank)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$draw_id, $name, $i + 1]);

        $poules[$i] = [
            'id'    => $pdo->lastInsertId(),
            'name'  => $name,
            'teams' => []
        ];
    }

    /* ============================================================
       🟦 5) MODE CHAMPIONNAT → toutes les équipes dans UNE poule
       ============================================================ */
    if ($type === "league") {

        $p = &$poules[0];
        $pos = 1;

        $stmtInsert = $pdo->prepare("
            INSERT INTO draw_teams (draw_id, poule_id, team_id, position_in_poule)
            VALUES (?, ?, ?, ?)
        ");

        foreach ($teams as $t) {
            $stmtInsert->execute([
                $draw_id,
                $p['id'],
                $t['id'],
                $pos
            ]);
            $p['teams'][] = $t;
            $pos++;
        }

        $pdo->commit();

        json_ok([
            'draw_id' => $draw_id,
            'type'    => $type,
            'label'   => $label,
            'poules'  => $poules
        ], "Championnat créé avec succès !");
        exit;
    }

    /* ============================================================
       🟥 6) MODE TIRAGE PAR POULES (NORMAL)
       ============================================================ */

    // 1️⃣ Têtes de série
    $pIndex = 0;
    foreach ($seeded as $t) {
        if ($pIndex >= $nb_poules) break;
        $poules[$pIndex]['teams'][] = $t;
        $pIndex++;
    }

    // 2️⃣ Autres équipes
    $pIndex = 0;
    foreach ($others as $t) {
        $poules[$pIndex]['teams'][] = $t;
        $pIndex = ($pIndex + 1) % $nb_poules;
    }

    // Enregistrer draw_teams
    $stmtInsert = $pdo->prepare("
        INSERT INTO draw_teams (draw_id, poule_id, team_id, position_in_poule)
        VALUES (?, ?, ?, ?)
    ");

    foreach ($poules as &$p) {
        $pos = 1;
        foreach ($p['teams'] as $t) {
            $stmtInsert->execute([
                $draw_id,
                $p['id'],
                $t['id'],
                $pos
            ]);
            $pos++;
        }
    }

    $pdo->commit();

    json_ok([
        'draw_id' => $draw_id,
        'type'    => $type,
        'label'   => $label,
        'poules'  => $poules
    ], "Tirage créé avec succès !");

} catch (Exception $e) {
    $pdo->rollBack();
    json_error("Erreur lors du tirage : " . $e->getMessage(), 500);
}
