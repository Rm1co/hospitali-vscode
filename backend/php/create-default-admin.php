<?php
header('Content-Type: application/json');
require_once 'DatabaseConnector.php';

try {
    $db = DatabaseConnector::getInstance();
    
    // Check if admin already exists
    $existing = $db->fetchOne('SELECT id FROM admins WHERE username = ?', ['admin']);
    
    if ($existing) {
        echo json_encode([
            'success' => true,
            'message' => 'Default admin account already exists'
        ]);
        exit;
    }
    
    // Create default admin: username = admin, password = admin123
    $passwordHash = password_hash('admin123', PASSWORD_DEFAULT);
    
    $db->insert('admins', [
        'username' => 'admin',
        'password_hash' => $passwordHash
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Default admin account created successfully. Username: admin, Password: admin123'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
