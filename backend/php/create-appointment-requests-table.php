<?php
/**
 * Create appointment_requests table
 */

header('Content-Type: application/json');

require_once 'DatabaseConnector.php';

$db = DatabaseConnector::getInstance();
$pdo = $db->getConnection();

try {
    $sql = "CREATE TABLE IF NOT EXISTS appointment_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        patient_id INT,
        department VARCHAR(100),
        preferred_date DATE,
        preferred_time VARCHAR(50),
        reason TEXT,
        status VARCHAR(50) DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
    )";
    
    $pdo->exec($sql);
    
    echo json_encode([
        'success' => true,
        'message' => 'Appointment requests table created successfully'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
