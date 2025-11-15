<?php
/**
 * Add notes column to appointments table
 */

header('Content-Type: application/json');

require_once 'DatabaseConnector.php';

$db = DatabaseConnector::getInstance();
$pdo = $db->getConnection();

try {
    // Check if column already exists
    $stmt = $pdo->query("SHOW COLUMNS FROM appointments LIKE 'notes'");
    $columnExists = $stmt->fetch();
    
    if ($columnExists) {
        echo json_encode([
            'success' => true,
            'message' => 'Notes column already exists'
        ]);
        exit;
    }
    
    // Add the notes column
    $pdo->exec("ALTER TABLE appointments ADD COLUMN notes TEXT AFTER status");
    
    echo json_encode([
        'success' => true,
        'message' => 'Notes column added successfully'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
