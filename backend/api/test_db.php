<?php
header('Content-Type: application/json');

$debug = [
    'env_vars' => [
        'DB_HOST' => getenv('DB_HOST') ? 'DETECHTED' : 'MISSING',
        'DB_NAME' => getenv('DB_NAME') ? 'DETECHTED' : 'MISSING',
        'DB_USER' => getenv('DB_USER') ? 'DETECHTED' : 'MISSING',
        'DB_PASS' => getenv('DB_PASS') ? 'DETECHTED' : 'MISSING',
    ],
    'connection_test' => null,
    'tables_found' => [],
];

if (in_array('MISSING', $debug['env_vars'])) {
    $debug['connection_test'] = "Error: Missing one or more environment variables on Vercel.";
} else {
    try {
        $dsn = "mysql:host=" . getenv('DB_HOST') . ";dbname=" . getenv('DB_NAME') . ";charset=utf8mb4";
        $pdo = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASS'), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5
        ]);
        $debug['connection_test'] = "SUCCESS: Connected to the database!";
        
        // Check tables
        $stmt = $pdo->query("SHOW TABLES");
        $debug['tables_found'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($debug['tables_found'])) {
            $debug['status_message'] = "WARNING: Database is connected but NO TABLES were found. Did you run the SQL script?";
        } else {
            $debug['status_message'] = "OK: Database is connected and tables exist.";
        }
        
    } catch (PDOException $e) {
        $debug['connection_test'] = "FAILED: " . $e->getMessage();
    }
}

echo json_encode($debug, JSON_PRETTY_PRINT);
