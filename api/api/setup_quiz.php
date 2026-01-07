<?php
// backend/api/setup_quiz.php
header('Content-Type: application/json');
require_once __DIR__ . '/../core/db.php';

try {
    $pdo = getPDO();

    $pdo->exec("CREATE TABLE IF NOT EXISTS quiz_questions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        question TEXT NOT NULL,
        option_a VARCHAR(255) NOT NULL,
        option_b VARCHAR(255) NOT NULL,
        option_c VARCHAR(255) NOT NULL,
        option_d VARCHAR(255) NOT NULL,
        correct_option TINYINT NOT NULL COMMENT '0=A, 1=B, 2=C, 3=D',
        difficulty ENUM('facile', 'moyen', 'difficile') DEFAULT 'moyen',
        category VARCHAR(100) DEFAULT 'Général',
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Insérer quelques questions par défaut
    $stmt = $pdo->query("SELECT COUNT(*) FROM quiz_questions");
    $count = $stmt->fetchColumn();

    if ($count == 0) {
        $defaultQuestions = [
            ["Quel joueur a remporté le plus de Ballons d'Or ?", "Cristiano Ronaldo", "Lionel Messi", "Michel Platini", "Johan Cruyff", 1, "difficile", "Histoire"],
            ["En quelle année la France a-t-elle remporté sa première Coupe du Monde ?", "1984", "1998", "2000", "2006", 1, "moyen", "Histoire"],
            ["Quel club a remporté le plus de Ligues des Champions ?", "FC Barcelone", "AC Milan", "Real Madrid", "Bayern Munich", 2, "moyen", "Clubs"],
            ["Qui est le meilleur buteur de l'histoire de la Coupe du Monde ?", "Pelé", "Ronaldo Nazário", "Miroslav Klose", "Gerd Müller", 2, "difficile", "Records"],
            ["Quel pays a organisé la Coupe du Monde 2014 ?", "Afrique du Sud", "Brésil", "Russie", "Allemagne", 1, "facile", "Histoire"],
            ["Combien de joueurs composent une équipe de football sur le terrain ?", "10", "11", "12", "9", 1, "facile", "Règles"],
            ["Quel joueur est surnommé 'CR7' ?", "Cristiano Ronaldo", "Carlos Ramos", "Clarence Seedorf", "Cafu", 0, "facile", "Joueurs"],
            ["Quelle est la durée réglementaire d'un match de football ?", "80 minutes", "90 minutes", "100 minutes", "120 minutes", 1, "facile", "Règles"],
            ["Quel pays a remporté la première Coupe du Monde en 1930 ?", "Brésil", "Argentine", "Uruguay", "Italie", 2, "moyen", "Histoire"],
            ["Quel club Zinedine Zidane a-t-il entraîné ?", "FC Barcelone", "Real Madrid", "Juventus", "Manchester United", 1, "moyen", "Entraîneurs"]
        ];

        $stmt = $pdo->prepare("INSERT INTO quiz_questions (question, option_a, option_b, option_c, option_d, correct_option, difficulty, category) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        foreach ($defaultQuestions as $q) {
            $stmt->execute($q);
        }
    }

    echo json_encode(["success" => true, "message" => "Table quiz_questions créée avec succès. $count questions existantes."]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
