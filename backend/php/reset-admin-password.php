<?php
header('Content-Type: application/json');
require_once 'DatabaseConnector.php';

try {
    $db = DatabaseConnector::getInstance();
    
    // Reset admin password to Admin@123
    $newPasswordHash = password_hash('Admin@123', PASSWORD_DEFAULT);
    
    $db->update('admins', [
        'password_hash' => $newPasswordHash,
        'full_name' => 'System Administrator',
        'email' => 'admin@telaviv-hospital.com'
    ], 'username = ?', ['admin']);
    
    // Verify the update worked
    $admin = $db->fetchOne('SELECT password_hash FROM admins WHERE username = ?', ['admin']);
    $verified = password_verify('Admin@123', $admin['password_hash']);
    
    echo json_encode([
        'success' => true,
        'message' => 'Admin password reset successfully',
        'verified' => $verified,
        'credentials' => [
            'username' => 'admin',
            'password' => 'Admin@123'
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
