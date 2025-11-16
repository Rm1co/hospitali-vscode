<?php
header('Content-Type: application/json');
require_once 'DatabaseConnector.php';

try {
    $db = DatabaseConnector::getInstance();
    
    // Get all admins
    $admins = $db->fetchAll('SELECT id, username, full_name, email, role, is_super_admin, is_active, created_at FROM admins');
    
    // Check if we can verify the password for 'admin' user
    $admin = $db->fetchOne('SELECT password_hash FROM admins WHERE username = ?', ['admin']);
    
    $passwordCheck = null;
    if ($admin) {
        $passwordCheck = [
            'hash_exists' => !empty($admin['password_hash']),
            'hash_length' => strlen($admin['password_hash']),
            'password_verify' => password_verify('Admin@123', $admin['password_hash'])
        ];
    }
    
    echo json_encode([
        'success' => true,
        'admins' => $admins,
        'password_check' => $passwordCheck
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
