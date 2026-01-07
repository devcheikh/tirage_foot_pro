-- ========================================================
-- SCHÉMA DE BASE DE DONNÉES - TIRAGE FOOT PRO
-- Généré pour migration Vercel + MySQL Cloud
-- ========================================================

-- 1. Table Utilisateurs (Admin)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'coach', 'scout') DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insertion Admin par défaut (admin / 123456)
-- Note: Le mot de passe doit être hashé. Ici pour l'exemple on met un hash bcrypt générique ou on laisse l'user le créer via une page d'inscription si elle existe.
-- Pour simplifier : '123456' hashé = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
INSERT IGNORE INTO users (username, password_hash, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');


-- 2. Table Catégories (Saisons / Ligues)
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    season VARCHAR(20) NOT NULL,
    league_logo_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Table Équipes
CREATE TABLE IF NOT EXISTS teams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category_id INT,
    logo_url VARCHAR(255) DEFAULT 'assets/img/teams/default.png',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- 4. Table Joueurs (Scouting & Stats)
CREATE TABLE IF NOT EXISTS players (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team_id INT,
    name VARCHAR(100) NOT NULL,
    position VARCHAR(50),
    number INT,
    birthdate DATE,
    photo_url VARCHAR(255) DEFAULT 'assets/img/players/default.jpg',
    
    -- Champs Scouting
    is_visible BOOLEAN DEFAULT FALSE,
    bio TEXT,
    height FLOAT,
    weight FLOAT,
    preferred_foot VARCHAR(20),
    skills TEXT,
    video_url VARCHAR(255),
    license_number VARCHAR(50) UNIQUE,
    
    -- Stats Globales
    matches_played INT DEFAULT 0,
    goals INT DEFAULT 0,
    assists INT DEFAULT 0,
    
    -- Social
    instagram VARCHAR(100),
    twitter VARCHAR(100),
    linkedin VARCHAR(100),
    motto VARCHAR(255),
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE
);

-- 5. Table Tirages (Draws)
CREATE TABLE IF NOT EXISTS draws (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    date_created DATE,
    category_id INT,
    status ENUM('draft', 'published') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 6. Table Poules (Groups)
CREATE TABLE IF NOT EXISTS poules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    draw_id INT NOT NULL,
    name VARCHAR(50) NOT NULL, -- "Groupe A", "Groupe B"
    FOREIGN KEY (draw_id) REFERENCES draws(id) ON DELETE CASCADE
);

-- 7. Table Matchs
CREATE TABLE IF NOT EXISTS matches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    draw_id INT,
    poule_id INT,
    team_home INT NOT NULL,
    team_away INT NOT NULL,
    score_home INT DEFAULT NULL,
    score_away INT DEFAULT NULL,
    match_date DATE,
    match_time TIME,
    location VARCHAR(255),
    is_played BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (team_home) REFERENCES teams(id) ON DELETE CASCADE,
    FOREIGN KEY (team_away) REFERENCES teams(id) ON DELETE CASCADE
);

-- 8. Table Classement (Standings)
CREATE TABLE IF NOT EXISTS standings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    poule_id INT,
    team_id INT,
    points INT DEFAULT 0,
    played INT DEFAULT 0,
    won INT DEFAULT 0,
    drawn INT DEFAULT 0,
    lost INT DEFAULT 0,
    gf INT DEFAULT 0, -- Buts Pour
    ga INT DEFAULT 0, -- Buts Contre
    gd INT DEFAULT 0, -- Différence
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE
);

-- 9. Boutique & Réservations & Quiz (Modules Extras)
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image_url VARCHAR(255),
    stock INT DEFAULT 0,
    category VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(255) NOT NULL,
    customer_phone VARCHAR(50) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'shipped', 'cancelled') DEFAULT 'pending',
    items_json TEXT, 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    address TEXT,
    map_url TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stadium_id INT NOT NULL,
    customer_name VARCHAR(255) NOT NULL,
    customer_phone VARCHAR(50) NOT NULL,
    reservation_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS quiz_questions (
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
);

CREATE TABLE IF NOT EXISTS system_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('reservation_new', 'reservation_status', 'order_new', 'order_status', 'cv_new') NOT NULL,
    target_id INT NOT NULL, 
    target_role ENUM('admin', 'customer') NOT NULL,
    customer_phone VARCHAR(50),
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ========================================================
-- DONNÉES DE DÉMONSTRATION (SEED)
-- ========================================================

-- Catégories
INSERT INTO categories (name, season) VALUES ('Ligue 1', '2024-2025'), ('U19 National', '2024-2025');

-- Équipes
INSERT INTO teams (name, category_id) VALUES 
('Paris SG', 1), ('Marseille', 1), ('Lyon', 1), ('Monaco', 1);

-- Joueurs Demo
INSERT INTO players (team_id, name, bio, position, number, is_visible) VALUES 
(1, 'Kylian M.', 'Attaquant vedette', 'Attaquant', 7, 1),
(2, 'Pierre-Emerick A.', 'Buteur expérimenté', 'Attaquant', 10, 1);

-- Quiz Questions Demo
INSERT INTO quiz_questions (question, option_a, option_b, option_c, option_d, correct_option, difficulty) VALUES
('Qui a gagné le plus de Ballons d\'Or ?', 'CR7', 'Messi', 'Platini', 'Zidane', 1, 'moyen');
