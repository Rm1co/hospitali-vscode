<?php
header('Content-Type: application/json');
require_once 'DatabaseConnector.php';

try {
    $db = DatabaseConnector::getInstance();
    $pdo = $db->getConnection();
    
    // Check if admins table exists
    $tables = $pdo->query("SHOW TABLES LIKE 'admins'")->fetchAll();
    if (empty($tables)) {
        $pdo->exec("
            CREATE TABLE admins (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(100) UNIQUE NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        echo json_encode([
            'success' => true,
            'message' => 'Admins table created successfully'
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'Admins table already exists'
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
