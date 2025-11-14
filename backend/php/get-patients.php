<?php
/**
 * get-patients.php
 * Returns all active patients for allocation
 */

header('Content-Type: application/json');

require_once 'DatabaseConnector.php';

$db = DatabaseConnector::getInstance();
$pdo = $db->getConnection();

try {
    $sql = "
        SELECT 
            p.id,
            p.first_name,
            p.last_name,
            p.phone,
            p.dob,
            p.gender,
            COUNT(a.id) as appointment_count
        FROM patients p
        LEFT JOIN appointments a ON p.id = a.patient_id
        WHERE p.id > 0
        GROUP BY p.id
        ORDER BY p.first_name, p.last_name
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $patients = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'patients' => $patients
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
