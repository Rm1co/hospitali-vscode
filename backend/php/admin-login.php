<?php
header('Content-Type: application/json');
require_once 'DatabaseConnector.php';

try {
    $db = DatabaseConnector::getInstance();
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
        exit;
    }
    
    $username = trim($input['username'] ?? '');
    $password = $input['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Username and password required']);
        exit;
    }
    
    // Fetch admin
    $admin = $db->fetchOne(
        'SELECT id, username, password_hash, full_name, email, role, permissions, is_super_admin, is_active FROM admins WHERE username = ?',
        [$username]
    );
    
    if (!$admin) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
        exit;
    }
    
    // Check if account is active
    if (!$admin['is_active']) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Account has been deactivated']);
        exit;
    }
    
    // Verify password
    if (!password_verify($password, $admin['password_hash'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
        exit;
    }
    
    // Update last login
    $db->update('admins', ['last_login' => date('Y-m-d H:i:s')], 'id = ?', [$admin['id']]);
    
    // Parse permissions JSON
    $permissions = $admin['permissions'] ? json_decode($admin['permissions'], true) : [];
    
    echo json_encode([
        'success' => true,
        'id' => (int)$admin['id'],
        'username' => $admin['username'],
        'full_name' => $admin['full_name'],
        'email' => $admin['email'],
        'role' => $admin['role'],
        'permissions' => $permissions,
        'is_super_admin' => (bool)$admin['is_super_admin']
    ]);
    
} catch (Exception $e) {
    error_log('Admin login error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>
