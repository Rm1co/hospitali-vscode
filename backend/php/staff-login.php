<?php

header('Content-Type: application/json');

require_once 'DatabaseConnector.php';

$db = DatabaseConnector::getInstance();

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['email']) || !isset($input['password'])) {
        throw new Exception('Missing email or password');
    }

    $email = filter_var($input['email'], FILTER_VALIDATE_EMAIL);
    if (!$email) {
        throw new Exception('Invalid email');
    }

    $staff = $db->fetchOne(
        'SELECT id, first_name, last_name, email, password_hash, role FROM staff WHERE email = ? AND is_active = TRUE',
        [$email]
    );

    if (!$staff) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid credentials'
        ]);
        exit;
    }

    if (!password_verify($input['password'], $staff['password_hash'])) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid credentials'
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'id' => (int)$staff['id'],
        'email' => $staff['email'],
        'name' => $staff['first_name'] . ' ' . $staff['last_name'],
        'role' => $staff['role']
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
