<?php
/**
 * get-all-doctors.php
 * Returns all doctors in the system
 */

header('Content-Type: application/json');

require_once 'DatabaseConnector.php';

$db = DatabaseConnector::getInstance();
$pdo = $db->getConnection();

try {
    $sql = "
        SELECT 
            s.id,
            s.first_name,
            s.last_name,
            s.role,
            s.department,
            s.phone,
            s.email,
            s.license_number,
            s.is_active,
            COUNT(a.id) as appointment_count
        FROM staff s
        LEFT JOIN appointments a ON s.id = a.staff_id 
            AND a.appointment_time > NOW()
            AND a.status NOT IN ('Cancelled', 'No-show')
        WHERE s.role = 'Doctor'
        GROUP BY s.id
        ORDER BY s.first_name, s.last_name
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $doctors = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'doctors' => $doctors
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
