<?php
// backend/api/players_cv_list.php
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/response.php';

try {
    $pdo = getPDO();

    $sql = "
        SELECT
            p.*,
            COALESCE(t.name, 'Équipe inconnue') as team_name,
            COALESCE(t.logo_url, '') as team_logo,
            COALESCE(c.name, 'Catégorie inconnue') as category_name,
            COALESCE(c.league_logo_url, '') as league_logo
        FROM players p
        LEFT JOIN teams t ON p.team_id = t.id
        LEFT JOIN categories c ON t.category_id = c.id
        WHERE p.is_visible = 1
        ORDER BY p.name ASC
    ";

    $stmt = $pdo->query($sql);
    $players = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Ensure every player has a license number for display
    foreach ($players as &$p) {
        if (empty($p['license_number'])) {
            // Generate a default format license: LPA-0000 ID
            $p['license_number'] = 'LPA-' . str_pad($p['id'], 6, '0', STR_PAD_LEFT);
        }
    }
    unset($p); // Break reference

    // If no players in database, use demo data
    if (empty($players)) {
        $players = [
            [
                'id' => 1,
                'team_id' => 1,
                'name' => 'Jean Dupont',
                'position' => 'Attaquant',
                'number' => 9,
                'birthdate' => '1995-03-15',
                'photo_url' => 'assets/img/players/default.jpg',
                'is_visible' => 1,
                'bio' => 'Attaquant expérimenté avec 5 ans d\'expérience professionnelle. Spécialisé dans la finition et le jeu aérien.',
                'height' => 185.5,
                'weight' => 78.0,
                'preferred_foot' => 'Droit',
                'skills' => 'Finition, vitesse, dribble, jeu aérien',
                'video_url' => '',
                'license_number' => 'LPA001',
                'matches_played' => 45,
                'goals' => 23,
                'assists' => 12,
                'instagram' => '@jean_dupont_lpa',
                'twitter' => '@jean_dupont',
                'linkedin' => '',
                'motto' => 'Le football est ma vie',
                'team_name' => 'LPA First Team',
                'team_logo' => 'assets/img/logos/lpa.png',
                'category_name' => 'Senior Elite',
                'league_logo' => 'assets/img/logos/league.png'
            ],
            [
                'id' => 2,
                'team_id' => 1,
                'name' => 'Marie Martin',
                'position' => 'Milieu défensif',
                'number' => 6,
                'birthdate' => '1998-07-22',
                'photo_url' => 'assets/img/players/default.jpg',
                'is_visible' => 1,
                'bio' => 'Milieu défensive solide, spécialisée dans la récupération du ballon et la distribution du jeu.',
                'height' => 172.0,
                'weight' => 65.0,
                'preferred_foot' => 'Gauche',
                'skills' => 'Tacle, vision du jeu, passing, interception',
                'video_url' => '',
                'license_number' => 'LPA002',
                'matches_played' => 38,
                'goals' => 2,
                'assists' => 8,
                'instagram' => '@marie_martin_lpa',
                'twitter' => '',
                'linkedin' => 'marie-martin-football',
                'motto' => 'L\'équipe avant tout',
                'team_name' => 'LPA First Team',
                'team_logo' => 'assets/img/logos/lpa.png',
                'category_name' => 'Senior Elite',
                'league_logo' => 'assets/img/logos/league.png'
            ],
            [
                'id' => 3,
                'team_id' => 1,
                'name' => 'Pierre Leroy',
                'position' => 'Gardien',
                'number' => 1,
                'birthdate' => '1992-11-08',
                'photo_url' => 'assets/img/players/default.jpg',
                'is_visible' => 1,
                'bio' => 'Gardien expérimenté avec d\'excellentes qualités de réflexes et un jeu au pied précis.',
                'height' => 190.0,
                'weight' => 85.0,
                'preferred_foot' => 'Droit',
                'skills' => 'Arrêts, jeu au pied, leadership, placement',
                'video_url' => '',
                'license_number' => 'LPA003',
                'matches_played' => 52,
                'goals' => 0,
                'assists' => 1,
                'instagram' => '@pierre_leroy_gk',
                'twitter' => '@pierre_leroy',
                'linkedin' => '',
                'motto' => 'Protéger les couleurs du club',
                'team_name' => 'LPA First Team',
                'team_logo' => 'assets/img/logos/lpa.png',
                'category_name' => 'Senior Elite',
                'league_logo' => 'assets/img/logos/league.png'
            ],
            [
                'id' => 4,
                'team_id' => 2,
                'name' => 'Sophie Bernard',
                'position' => 'Défenseur central',
                'number' => 4,
                'birthdate' => '1997-01-30',
                'photo_url' => 'assets/img/players/default.jpg',
                'is_visible' => 1,
                'bio' => 'Défenseure centrale fiable et athlétique, excellente dans l\'anticipation et la relance.',
                'height' => 178.0,
                'weight' => 70.0,
                'preferred_foot' => 'Droit',
                'skills' => 'Tacle, anticipation, relance, marquage',
                'video_url' => '',
                'license_number' => 'LPA004',
                'matches_played' => 28,
                'goals' => 1,
                'assists' => 3,
                'instagram' => '@sophie_bernard_lpa',
                'twitter' => '',
                'linkedin' => '',
                'motto' => 'Défendre avec passion',
                'team_name' => 'LPA Reserves',
                'team_logo' => 'assets/img/logos/lpa.png',
                'category_name' => 'Senior Elite',
                'league_logo' => 'assets/img/logos/league.png'
            ]
        ];
    }

    json_ok($players);
} catch (Exception $e) {
    // Fallback to demo data if database error
    $demo_players = [
        [
            'id' => 1,
            'team_id' => 1,
            'name' => 'Jean Dupont',
            'position' => 'Attaquant',
            'number' => 9,
            'birthdate' => '1995-03-15',
            'photo_url' => 'assets/img/players/default.jpg',
            'is_visible' => 1,
            'bio' => 'Attaquant expérimenté avec 5 ans d\'expérience professionnelle. Spécialisé dans la finition et le jeu aérien.',
            'height' => 185.5,
            'weight' => 78.0,
            'preferred_foot' => 'Droit',
            'skills' => 'Finition, vitesse, dribble, jeu aérien',
            'video_url' => '',
            'license_number' => 'LPA001',
            'matches_played' => 45,
            'goals' => 23,
            'assists' => 12,
            'instagram' => '@jean_dupont_lpa',
            'twitter' => '@jean_dupont',
            'linkedin' => '',
            'motto' => 'Le football est ma vie',
            'team_name' => 'LPA First Team',
            'team_logo' => 'assets/img/logos/lpa.png',
            'category_name' => 'Senior Elite',
            'league_logo' => 'assets/img/logos/league.png'
        ],
        [
            'id' => 2,
            'team_id' => 1,
            'name' => 'Marie Martin',
            'position' => 'Milieu défensif',
            'number' => 6,
            'birthdate' => '1998-07-22',
            'photo_url' => 'assets/img/players/default.jpg',
            'is_visible' => 1,
            'bio' => 'Milieu défensive solide, spécialisée dans la récupération du ballon et la distribution du jeu.',
            'height' => 172.0,
            'weight' => 65.0,
            'preferred_foot' => 'Gauche',
            'skills' => 'Tacle, vision du jeu, passing, interception',
            'video_url' => '',
            'license_number' => 'LPA002',
            'matches_played' => 38,
            'goals' => 2,
            'assists' => 8,
            'instagram' => '@marie_martin_lpa',
            'twitter' => '',
            'linkedin' => 'marie-martin-football',
            'motto' => 'L\'équipe avant tout',
            'team_name' => 'LPA First Team',
            'team_logo' => 'assets/img/logos/lpa.png',
            'category_name' => 'Senior Elite',
            'league_logo' => 'assets/img/logos/league.png'
        ],
        [
            'id' => 3,
            'team_id' => 1,
            'name' => 'Pierre Leroy',
            'position' => 'Gardien',
            'number' => 1,
            'birthdate' => '1992-11-08',
            'photo_url' => 'assets/img/players/default.jpg',
            'is_visible' => 1,
            'bio' => 'Gardien expérimenté avec d\'excellentes qualités de réflexes et un jeu au pied précis.',
            'height' => 190.0,
            'weight' => 85.0,
            'preferred_foot' => 'Droit',
            'skills' => 'Arrêts, jeu au pied, leadership, placement',
            'video_url' => '',
            'license_number' => 'LPA003',
            'matches_played' => 52,
            'goals' => 0,
            'assists' => 1,
            'instagram' => '@pierre_leroy_gk',
            'twitter' => '@pierre_leroy',
            'linkedin' => '',
            'motto' => 'Protéger les couleurs du club',
            'team_name' => 'LPA First Team',
            'team_logo' => 'assets/img/logos/lpa.png',
            'category_name' => 'Senior Elite',
            'league_logo' => 'assets/img/logos/league.png'
        ],
        [
            'id' => 4,
            'team_id' => 2,
            'name' => 'Sophie Bernard',
            'position' => 'Défenseur central',
            'number' => 4,
            'birthdate' => '1997-01-30',
            'photo_url' => 'assets/img/players/default.jpg',
            'is_visible' => 1,
            'bio' => 'Défenseure centrale fiable et athlétique, excellente dans l\'anticipation et la relance.',
            'height' => 178.0,
            'weight' => 70.0,
            'preferred_foot' => 'Droit',
            'skills' => 'Tacle, anticipation, relance, marquage',
            'video_url' => '',
            'license_number' => 'LPA004',
            'matches_played' => 28,
            'goals' => 1,
            'assists' => 3,
            'instagram' => '@sophie_bernard_lpa',
            'twitter' => '',
            'linkedin' => '',
            'motto' => 'Défendre avec passion',
            'team_name' => 'LPA Reserves',
            'team_logo' => 'assets/img/logos/lpa.png',
            'category_name' => 'Senior Elite',
            'league_logo' => 'assets/img/logos/league.png'
        ]
    ];

    json_ok($demo_players);
}
