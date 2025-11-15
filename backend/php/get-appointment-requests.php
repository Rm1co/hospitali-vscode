<?php
/**
 * Get all appointment requests for secretary
 */

header('Content-Type: application/json');

require_once 'DatabaseConnector.php';

$db = DatabaseConnector::getInstance();
$pdo = $db->getConnection();

try {
    $status = $_GET['status'] ?? 'Pending';
    
    $sql = "
        SELECT 
            ar.id,
            ar.patient_id,
            ar.department,
            ar.preferred_date,
            ar.preferred_time,
            ar.reason,
            ar.status,
            ar.created_at,
            CONCAT(p.first_name, ' ', p.last_name) as patient_name,
            p.phone as patient_phone,
            pa.email as patient_email
        FROM appointment_requests ar
        JOIN patients p ON ar.patient_id = p.id
        LEFT JOIN patient_accounts pa ON p.id = pa.patient_id
        WHERE ar.status = ?
        ORDER BY ar.created_at DESC
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$status]);
    $requests = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'requests' => $requests
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
