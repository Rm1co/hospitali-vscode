<?php
header('Content-Type: application/json');
require_once 'DatabaseConnector.php';

try {
    $db = DatabaseConnector::getInstance();
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid request data']);
        exit;
    }
    
    $firstName = trim($input['first_name'] ?? '');
    $lastName = trim($input['last_name'] ?? '');
    $email = trim($input['email'] ?? '');
    $phone = trim($input['phone'] ?? '');
    $role = trim($input['role'] ?? '');
    $department = trim($input['department'] ?? '');
    
    // Validation
    if (empty($firstName) || empty($lastName) || empty($email) || empty($role) || empty($department)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'All required fields must be filled']);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit;
    }
    
    // Check if email already exists
    $existingStaff = $db->fetchOne('SELECT id FROM staff WHERE email = ?', [$email]);
    if ($existingStaff) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Email already registered']);
        exit;
    }
    
    // Generate temporary password and activation token
    $tempPassword = bin2hex(random_bytes(8)); // 16 character password
    $activationToken = bin2hex(random_bytes(32)); // 64 character token
    $passwordHash = password_hash($tempPassword, PASSWORD_DEFAULT);
    
    // Insert staff member
    $staffId = $db->insert('staff', [
        'first_name' => $firstName,
        'last_name' => $lastName,
        'email' => $email,
        'phone' => $phone,
        'role' => $role,
        'department' => $department,
        'password_hash' => $passwordHash,
        'activation_token' => $activationToken,
        'is_activated' => 0
    ]);
    
    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Staff member added successfully',
        'staff_id' => $staffId,
        'email' => $email,
        'temp_password' => $tempPassword,
        'activation_token' => $activationToken
    ]);
    
} catch (Exception $e) {
    error_log('Admin add staff error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error. Please try again later.'
    ]);
}
?>
