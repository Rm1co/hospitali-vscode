<?php
header('Content-Type: application/json');
require_once 'DatabaseConnector.php';

try {
    $db = DatabaseConnector::getInstance();
    
    $pending = isset($_GET['pending']) && $_GET['pending'] === 'true';
    
    $sql = 'SELECT id, first_name, last_name, email, phone, role, department, is_activated, created_at FROM staff';
    
    if ($pending) {
        $sql .= ' WHERE is_activated = 0';
    }
    
    $sql .= ' ORDER BY created_at DESC';
    
    $staff = $db->fetchAll($sql);
    
    echo json_encode([
        'success' => true,
        'staff' => $staff
    ]);
    
} catch (Exception $e) {
    error_log('Get staff error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error'
    ]);
}
?>
