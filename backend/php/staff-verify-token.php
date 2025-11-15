<?php
header('Content-Type: application/json');
require_once 'DatabaseConnector.php';

try {
    $db = DatabaseConnector::getInstance();
    
    $token = $_GET['token'] ?? '';
    
    if (empty($token)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Token required']);
        exit;
    }
    
    $staff = $db->fetchOne(
        'SELECT email, is_activated FROM staff WHERE activation_token = ?',
        [$token]
    );
    
    if (!$staff) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Invalid token']);
        exit;
    }
    
    if ($staff['is_activated']) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Account already activated']);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'email' => $staff['email']
    ]);
    
} catch (Exception $e) {
    error_log('Token verification error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>
