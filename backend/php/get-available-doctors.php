<?php

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
            s.email
        FROM staff s
        WHERE s.role = 'Doctor' 
            AND s.is_activated = 1
        ORDER BY s.first_name, s.last_name
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $doctors = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'doctors' => $doctors,
        'message' => 'Registered doctors retrieved'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error retrieving doctors: ' . $e->getMessage()
    ]);
}
?>
