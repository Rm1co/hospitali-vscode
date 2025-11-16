<?php
header('Content-Type: application/json');
require_once 'DatabaseConnector.php';

try {
    $db = DatabaseConnector::getInstance();
    
    // Check if any Super Admin exists
    $existing = $db->fetchOne('SELECT id FROM admins WHERE is_super_admin = TRUE');
    
    if ($existing) {
        echo json_encode([
            'success' => true,
            'message' => 'Super Admin account already exists'
        ]);
        exit;
    }
    
    // Create default Super Admin: username = admin, password = Admin@123
    $passwordHash = password_hash('Admin@123', PASSWORD_DEFAULT);
    
    // Super Admin permissions
    $permissions = json_encode([
        'manage_admins' => true,
        'manage_staff' => true,
        'manage_patients' => true,
        'manage_appointments' => true,
        'manage_inventory' => true,
        'manage_billing' => true,
        'manage_pharmacy' => true,
        'manage_lab' => true,
        'manage_wards' => true,
        'view_all_reports' => true,
        'system_settings' => true,
        'backup_restore' => true
    ]);
    
    $db->insert('admins', [
        'username' => 'admin',
        'password_hash' => $passwordHash,
        'full_name' => 'System Administrator',
        'email' => 'admin@telaviv-hospital.com',
        'role' => 'Super Admin',
        'permissions' => $permissions,
        'is_super_admin' => 1,
        'is_active' => 1
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Super Admin account created successfully. Username: admin, Password: Admin@123'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
