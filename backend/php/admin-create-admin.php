<?php
/**
 * Super Admin creates new admin accounts with specific roles
 */

header('Content-Type: application/json');
require_once 'DatabaseConnector.php';
require_once 'EmailService.php';

try {
    $db = DatabaseConnector::getInstance();
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid request data']);
        exit;
    }
    
    // Verify super admin access
    $creatorId = (int)($input['creator_id'] ?? 0);
    $creator = $db->fetchOne('SELECT is_super_admin FROM admins WHERE id = ?', [$creatorId]);
    
    if (!$creator || !$creator['is_super_admin']) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Only Super Admin can create admin accounts']);
        exit;
    }
    
    $username = trim($input['username'] ?? '');
    $fullName = trim($input['full_name'] ?? '');
    $email = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';
    $role = trim($input['role'] ?? '');
    
    // Validation
    if (empty($username) || empty($fullName) || empty($email) || empty($password) || empty($role)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit;
    }
    
    if (strlen($password) < 8) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters']);
        exit;
    }
    
    // Check if username or email exists
    $existing = $db->fetchOne('SELECT id FROM admins WHERE username = ? OR email = ?', [$username, $email]);
    if ($existing) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Username or email already exists']);
        exit;
    }
    
    // Define role-based permissions
    $rolePermissions = [
        'Super Admin' => [
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
        ],
        'HR Admin' => [
            'manage_staff' => true,
            'view_staff_reports' => true,
            'manage_departments' => true
        ],
        'Reception/Admin Clerk' => [
            'manage_patients' => true,
            'manage_appointments' => true,
            'view_patient_reports' => true
        ],
        'Finance Admin' => [
            'manage_billing' => true,
            'view_financial_reports' => true,
            'manage_invoices' => true
        ],
        'Pharmacy Admin' => [
            'manage_pharmacy' => true,
            'manage_pharmacy_inventory' => true,
            'view_pharmacy_reports' => true
        ],
        'Lab Admin' => [
            'manage_lab' => true,
            'manage_lab_tests' => true,
            'view_all_lab_reports' => true,
            'approve_lab_results' => true,
            'edit_any_lab_results' => true,
            'delete_lab_results' => true,
            'manage_lab_staff' => true,
            'configure_lab_settings' => true
        ],
        'Ward/Maternity Admin' => [
            'manage_wards' => true,
            'manage_beds' => true,
            'view_ward_reports' => true
        ]
    ];
    
    $permissions = $rolePermissions[$role] ?? [];
    $isSuperAdmin = ($role === 'Super Admin') ? 1 : 0;
    
    // Store the plain password for email (before hashing)
    $temporaryPassword = $password;
    
    // Hash password
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new admin
    $adminId = $db->insert('admins', [
        'username' => $username,
        'password_hash' => $passwordHash,
        'full_name' => $fullName,
        'email' => $email,
        'role' => $role,
        'permissions' => json_encode($permissions),
        'is_super_admin' => $isSuperAdmin,
        'is_active' => 1,
        'created_by' => $creatorId
    ]);
    
    // Send email notification to the new admin
    $emailSent = false;
    try {
        $emailSent = EmailService::sendAdminCreationEmail(
            $email,
            $fullName,
            $username,
            $temporaryPassword,
            $role
        );
    } catch (Exception $emailError) {
        error_log('Email sending error: ' . $emailError->getMessage());
    }
    
    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Admin account created successfully' . ($emailSent ? ' and email sent' : ' (email notification failed)'),
        'admin_id' => $adminId,
        'username' => $username,
        'role' => $role,
        'email_sent' => $emailSent
    ]);
    
} catch (Exception $e) {
    error_log('Create admin error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error. Please try again later.'
    ]);
}
?>
