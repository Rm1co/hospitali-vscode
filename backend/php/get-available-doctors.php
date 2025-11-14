<?php

header('Content-Type: application/json');

require_once 'DatabaseConnector.php';

$db = DatabaseConnector::getInstance();
$pdo = $db->getConnection();

try {
    $sql = "
        SELECT DISTINCT
            s.id,
            s.first_name,
            s.last_name,
            s.role,
            s.department,
            s.phone,
            s.email,
            COUNT(a.id) as appointment_count
        FROM staff s
        LEFT JOIN appointments a ON s.id = a.staff_id 
            AND a.appointment_time > NOW() 
            AND a.appointment_time < DATE_ADD(NOW(), INTERVAL 30 MINUTE)
            AND a.status NOT IN ('Cancelled', 'No-show')
        WHERE s.role = 'Doctor' 
            AND s.is_active = TRUE
        GROUP BY s.id
        HAVING appointment_count = 0
        ORDER BY s.first_name, s.last_name
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $doctors = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'doctors' => $doctors,
        'message' => 'Available doctors retrieved'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error retrieving doctors: ' . $e->getMessage()
    ]);
}
?>
