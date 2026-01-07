<?php
// backend/api/seed_players.php
header('Content-Type: application/json');
require_once __DIR__ . '/../core/db.php';

try {
    $pdo = getPDO();

    // First, ensure we have some teams
    $teamCount = $pdo->query("SELECT COUNT(*) FROM teams")->fetchColumn();
    if ($teamCount == 0) {
        // Insert some demo teams
        $teams = [
            ['name' => 'LPA First Team', 'category_id' => 1],
            ['name' => 'LPA Reserves', 'category_id' => 1],
            ['name' => 'LPA U19', 'category_id' => 2]
        ];

        $stmt = $pdo->prepare("INSERT INTO teams (name, category_id) VALUES (?, ?)");
        foreach ($teams as $team) {
            $stmt->execute([$team['name'], $team['category_id']]);
        }
    }

    // Insert demo players
    $players = [
        [
            'team_id' => 1,
            'name' => 'Jean Dupont',
            'position' => 'Attaquant',
            'number' => 9,
            'birthdate' => '1995-03-15',
            'photo_url' => 'assets/img/players/default.jpg',
            'is_visible' => 1,
            'bio' => 'Attaquant expérimenté avec 5 ans d\'expérience professionnelle.',
            'height' => 185.5,
            'weight' => 78.0,
            'preferred_foot' => 'Droit',
            'skills' => 'Finition, vitesse, dribble',
            'video_url' => '',
            'license_number' => 'LPA001',
            'matches_played' => 45,
            'goals' => 23,
            'assists' => 12,
            'instagram' => '@jean_dupont_lpa',
            'twitter' => '@jean_dupont',
            'linkedin' => '',
            'motto' => 'Le football est ma vie'
        ],
        [
            'team_id' => 1,
            'name' => 'Marie Martin',
            'position' => 'Milieu défensif',
            'number' => 6,
            'birthdate' => '1998-07-22',
            'photo_url' => 'assets/img/players/default.jpg',
            'is_visible' => 1,
            'bio' => 'Milieu défensive solide, spécialisée dans la récupération du ballon.',
            'height' => 172.0,
            'weight' => 65.0,
            'preferred_foot' => 'Gauche',
            'skills' => 'Tacle, vision du jeu, passing',
            'video_url' => '',
            'license_number' => 'LPA002',
            'matches_played' => 38,
            'goals' => 2,
            'assists' => 8,
            'instagram' => '@marie_martin_lpa',
            'twitter' => '',
            'linkedin' => 'marie-martin-football',
            'motto' => 'L\'équipe avant tout'
        ],
        [
            'team_id' => 1,
            'name' => 'Pierre Leroy',
            'position' => 'Gardien',
            'number' => 1,
            'birthdate' => '1992-11-08',
            'photo_url' => 'assets/img/players/default.jpg',
            'is_visible' => 1,
            'bio' => 'Gardien expérimenté avec d\'excellentes qualités de réflexes.',
            'height' => 190.0,
            'weight' => 85.0,
            'preferred_foot' => 'Droit',
            'skills' => 'Arrêts, jeu au pied, leadership',
            'video_url' => '',
            'license_number' => 'LPA003',
            'matches_played' => 52,
            'goals' => 0,
            'assists' => 1,
            'instagram' => '@pierre_leroy_gk',
            'twitter' => '@pierre_leroy',
            'linkedin' => '',
            'motto' => 'Protéger les couleurs du club'
        ],
        [
            'team_id' => 2,
            'name' => 'Sophie Bernard',
            'position' => 'Défenseur central',
            'number' => 4,
            'birthdate' => '1997-01-30',
            'photo_url' => 'assets/img/players/default.jpg',
            'is_visible' => 1,
            'bio' => 'Défenseure centrale fiable et athlétique.',
            'height' => 178.0,
            'weight' => 70.0,
            'preferred_foot' => 'Droit',
            'skills' => 'Tacle, anticipation, relance',
            'video_url' => '',
            'license_number' => 'LPA004',
            'matches_played' => 28,
            'goals' => 1,
            'assists' => 3,
            'instagram' => '@sophie_bernard_lpa',
            'twitter' => '',
            'linkedin' => '',
            'motto' => 'Défendre avec passion'
        ]
    ];

    $stmt = $pdo->prepare("INSERT INTO players (team_id, name, position, number, birthdate, photo_url, is_visible, bio, height, weight, preferred_foot, skills, video_url, license_number, matches_played, goals, assists, instagram, twitter, linkedin, motto) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    foreach ($players as $player) {
        $stmt->execute([
            $player['team_id'], $player['name'], $player['position'], $player['number'],
            $player['birthdate'], $player['photo_url'], $player['is_visible'], $player['bio'],
            $player['height'], $player['weight'], $player['preferred_foot'], $player['skills'],
            $player['video_url'], $player['license_number'], $player['matches_played'],
            $player['goals'], $player['assists'], $player['instagram'], $player['twitter'],
            $player['linkedin'], $player['motto']
        ]);
    }

    echo json_encode(["success" => true, "message" => "Joueurs de démonstration ajoutés avec succès !"]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>