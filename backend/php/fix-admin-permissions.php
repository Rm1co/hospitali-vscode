<?php
header('Content-Type: application/json');
require_once 'DatabaseConnector.php';

try {
    $db = DatabaseConnector::getInstance();
    
    // Set proper Super Admin permissions
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
    
    $db->update('admins', [
        'permissions' => $permissions
    ], 'username = ?', ['admin']);
    
    echo json_encode([
        'success' => true,
        'message' => 'Super Admin permissions updated successfully'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
