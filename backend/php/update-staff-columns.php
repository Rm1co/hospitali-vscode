<?php
header('Content-Type: application/json');
require_once 'DatabaseConnector.php';

try {
    $db = DatabaseConnector::getInstance();
    $pdo = $db->getConnection();
    
    $messages = [];
    
    // Check if email column exists
    $columns = $pdo->query("SHOW COLUMNS FROM staff LIKE 'email'")->fetchAll();
    if (empty($columns)) {
        $pdo->exec("ALTER TABLE staff ADD COLUMN email VARCHAR(255) UNIQUE AFTER last_name");
        $messages[] = "Added email column";
    } else {
        $messages[] = "Email column already exists";
    }
    
    // Check if password_hash column exists
    $columns = $pdo->query("SHOW COLUMNS FROM staff LIKE 'password_hash'")->fetchAll();
    if (empty($columns)) {
        $pdo->exec("ALTER TABLE staff ADD COLUMN password_hash VARCHAR(255) AFTER department");
        $messages[] = "Added password_hash column";
    } else {
        $messages[] = "Password_hash column already exists";
    }
    
    // Check if activation_token column exists
    $columns = $pdo->query("SHOW COLUMNS FROM staff LIKE 'activation_token'")->fetchAll();
    if (empty($columns)) {
        $pdo->exec("ALTER TABLE staff ADD COLUMN activation_token VARCHAR(64) UNIQUE AFTER password_hash");
        $messages[] = "Added activation_token column";
    } else {
        $messages[] = "activation_token column already exists";
    }
    
    // Check if is_activated column exists
    $columns = $pdo->query("SHOW COLUMNS FROM staff LIKE 'is_activated'")->fetchAll();
    if (empty($columns)) {
        $pdo->exec("ALTER TABLE staff ADD COLUMN is_activated BOOLEAN DEFAULT FALSE AFTER activation_token");
        $messages[] = "Added is_activated column";
    } else {
        $messages[] = "is_activated column already exists";
    }
    
    echo json_encode([
        'success' => true,
        'message' => implode('. ', $messages)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
