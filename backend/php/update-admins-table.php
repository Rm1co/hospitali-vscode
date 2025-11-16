<?php
/**
 * Update admins table to support hierarchical roles and permissions
 */

header('Content-Type: application/json');
require_once 'DatabaseConnector.php';

try {
    $db = DatabaseConnector::getInstance();
    $pdo = $db->getConnection();
    
    $messages = [];
    
    // Check and add columns one by one
    $columns = [
        ['name' => 'full_name', 'definition' => 'VARCHAR(200) AFTER password_hash'],
        ['name' => 'email', 'definition' => 'VARCHAR(255) UNIQUE AFTER full_name'],
        ['name' => 'role', 'definition' => "VARCHAR(50) NOT NULL DEFAULT 'HR Admin' AFTER email"],
        ['name' => 'permissions', 'definition' => 'JSON AFTER role'],
        ['name' => 'is_super_admin', 'definition' => 'BOOLEAN DEFAULT FALSE AFTER permissions'],
        ['name' => 'is_active', 'definition' => 'BOOLEAN DEFAULT TRUE AFTER is_super_admin'],
        ['name' => 'created_by', 'definition' => 'INT AFTER is_active'],
        ['name' => 'last_login', 'definition' => 'TIMESTAMP NULL AFTER created_at']
    ];
    
    foreach ($columns as $column) {
        $stmt = $pdo->query("SHOW COLUMNS FROM admins LIKE '{$column['name']}'");
        $exists = $stmt->fetch();
        
        if (!$exists) {
            $pdo->exec("ALTER TABLE admins ADD COLUMN {$column['name']} {$column['definition']}");
            $messages[] = "Added {$column['name']} column";
        } else {
            $messages[] = "{$column['name']} column already exists";
        }
    }
    
    // Update existing admin records to be super admin
    $pdo->exec("UPDATE admins SET is_super_admin = TRUE, role = 'Super Admin' WHERE is_super_admin IS NULL OR is_super_admin = FALSE");
    $messages[] = "Updated existing admins to Super Admin";
    
    echo json_encode([
        'success' => true,
        'message' => implode('. ', $messages)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
