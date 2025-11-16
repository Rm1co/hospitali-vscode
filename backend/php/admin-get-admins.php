<?php
/**
 * Get all admins (Super Admin only)
 */

header('Content-Type: application/json');
require_once 'DatabaseConnector.php';

try {
    $db = DatabaseConnector::getInstance();
    
    // Verify super admin access
    $requesterId = (int)($_GET['requester_id'] ?? 0);
    $requester = $db->fetchOne('SELECT is_super_admin FROM admins WHERE id = ?', [$requesterId]);
    
    if (!$requester || !$requester['is_super_admin']) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Only Super Admin can view all admins']);
        exit;
    }
    
    $sql = 'SELECT id, username, full_name, email, role, is_super_admin, is_active, created_at, last_login FROM admins ORDER BY created_at DESC';
    
    $admins = $db->fetchAll($sql);
    
    echo json_encode([
        'success' => true,
        'admins' => $admins
    ]);
    
} catch (Exception $e) {
    error_log('Get admins error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error'
    ]);
}
?>
