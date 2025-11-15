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
    
    $email = trim($input['email'] ?? '');
    $tempPassword = $input['temp_password'] ?? '';
    $newPassword = $input['new_password'] ?? '';
    $activationToken = $input['activation_token'] ?? '';
    
    if (empty($email) || empty($tempPassword) || empty($newPassword)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }
    
    if (strlen($newPassword) < 6) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'New password must be at least 6 characters']);
        exit;
    }
    
    // Fetch staff member
    $staff = $db->fetchOne(
        'SELECT id, password_hash, is_activated, activation_token FROM staff WHERE email = ?',
        [$email]
    );
    
    if (!$staff) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Staff member not found']);
        exit;
    }
    
    if ($staff['is_activated']) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Account already activated. Please login.']);
        exit;
    }
    
    // Verify temporary password
    if (!password_verify($tempPassword, $staff['password_hash'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid temporary password']);
        exit;
    }
    
    // Verify activation token if provided
    if (!empty($activationToken) && $staff['activation_token'] !== $activationToken) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid activation token']);
        exit;
    }
    
    // Update with new password and activate
    $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
    $db->update('staff', [
        'password_hash' => $newPasswordHash,
        'is_activated' => 1
    ], 'id = ?', [$staff['id']]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Account activated successfully'
    ]);
    
} catch (Exception $e) {
    error_log('Staff activation error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>
