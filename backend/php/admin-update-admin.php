<?php
/**
 * Update admin account (Super Admin only)
 */

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
    
    // Verify super admin access
    $requesterId = (int)($input['requester_id'] ?? 0);
    $requester = $db->fetchOne('SELECT is_super_admin FROM admins WHERE id = ?', [$requesterId]);
    
    if (!$requester || !$requester['is_super_admin']) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Only Super Admin can update admin accounts']);
        exit;
    }
    
    $adminId = (int)($input['admin_id'] ?? 0);
    $action = $input['action'] ?? '';
    
    if ($action === 'deactivate') {
        $db->update('admins', ['is_active' => 0], 'id = ?', [$adminId]);
        echo json_encode(['success' => true, 'message' => 'Admin account deactivated']);
    } elseif ($action === 'activate') {
        $db->update('admins', ['is_active' => 1], 'id = ?', [$adminId]);
        echo json_encode(['success' => true, 'message' => 'Admin account activated']);
    } elseif ($action === 'delete') {
        // Cannot delete super admin
        $admin = $db->fetchOne('SELECT is_super_admin FROM admins WHERE id = ?', [$adminId]);
        if ($admin && $admin['is_super_admin']) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Cannot delete Super Admin account']);
            exit;
        }
        
        $db->delete('admins', 'id = ?', [$adminId]);
        echo json_encode(['success' => true, 'message' => 'Admin account deleted']);
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    
} catch (Exception $e) {
    error_log('Update admin error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>
