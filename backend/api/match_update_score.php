<?php
// backend/api/match_update_score.php
require_once __DIR__ . '/../auth/check_auth.php';
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/response.php';

$input = json_decode(file_get_contents('php://input'), true);

$match_id   = intval($input['match_id'] ?? 0);
$score_home = $input['score_home'] ?? null;
$score_away = $input['score_away'] ?? null;

if ($match_id <= 0 || $score_home === null || $score_away === null) {
    json_error("match_id, score_home et score_away sont obligatoires.");
}

$score_home = intval($score_home);
$score_away = intval($score_away);

$pdo = getPDO();

// Récupérer match + poule
$stmt = $pdo->prepare("SELECT * FROM matches WHERE id = ?");
$stmt->execute([$match_id]);
$match = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$match) {
    json_error("Match introuvable.");
}

$poule_id = intval($match['poule_id']);

$pdo->beginTransaction();
try {
    // 1) Mettre à jour le score
    $stmtUp = $pdo->prepare("
        UPDATE matches
        SET score_home = ?, score_away = ?, status = 'played'
        WHERE id = ?
    ");
    $stmtUp->execute([$score_home, $score_away, $match_id]);

    // 2) Recalculer le classement de la poule
    // On efface les standings de la poule, puis on reconstruit à partir des matchs joués
    $stmtDel = $pdo->prepare("DELETE FROM standings WHERE poule_id = ?");
    $stmtDel->execute([$poule_id]);

    // On reconstruit en mémoire
    $stmtMatches = $pdo->prepare("
        SELECT * FROM matches
        WHERE poule_id = ? AND status = 'played'
    ");
    $stmtMatches->execute([$poule_id]);
    $matches = $stmtMatches->fetchAll(PDO::FETCH_ASSOC);

    $table = []; // [team_id => stats]

    foreach ($matches as $m) {
        $home = $m['team_home'];
        $away = $m['team_away'];
        $sh   = intval($m['score_home']);
        $sa   = intval($m['score_away']);

        // initialiser
        foreach ([$home, $away] as $tid) {
            if (!isset($table[$tid])) {
                $table[$tid] = [
                    'played'        => 0,
                    'wins'          => 0,
                    'draws'         => 0,
                    'losses'        => 0,
                    'goals_for'     => 0,
                    'goals_against' => 0,
                    'points'        => 0,
                ];
            }
        }

        // mis à jour stats
        $table[$home]['played']++;
        $table[$away]['played']++;

        $table[$home]['goals_for']     += $sh;
        $table[$home]['goals_against'] += $sa;

        $table[$away]['goals_for']     += $sa;
        $table[$away]['goals_against'] += $sh;

        if ($sh > $sa) {
            // victoire domicile
            $table[$home]['wins']++;
            $table[$away]['losses']++;
            $table[$home]['points'] += 3;
        } elseif ($sh < $sa) {
            // victoire extérieur
            $table[$away]['wins']++;
            $table[$home]['losses']++;
            $table[$away]['points'] += 3;
        } else {
            // match nul
            $table[$home]['draws']++;
            $table[$away]['draws']++;
            $table[$home]['points']++;
            $table[$away]['points']++;
        }
    }

    // 3) Insérer standings
    $stmtIns = $pdo->prepare("
        INSERT INTO standings (
            poule_id, team_id, played, wins, draws, losses,
            goals_for, goals_against, goal_diff, points
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    foreach ($table as $team_id => $st) {
        $gf = $st['goals_for'];
        $ga = $st['goals_against'];
        $gd = $gf - $ga;

        $stmtIns->execute([
            $poule_id,
            $team_id,
            $st['played'],
            $st['wins'],
            $st['draws'],
            $st['losses'],
            $gf,
            $ga,
            $gd,
            $st['points']
        ]);
    }

    $pdo->commit();

    json_ok(null, "Score mis à jour et classement recalculé.");

} catch (Exception $e) {
    $pdo->rollBack();
    json_error("Erreur lors de la mise à jour du score : " . $e->getMessage(), 500);
}
