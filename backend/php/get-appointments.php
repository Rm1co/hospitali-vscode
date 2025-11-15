<?php
/**
 * get-appointments.php
 * Returns all appointments with patient and doctor details
 */

header('Content-Type: application/json');

require_once 'DatabaseConnector.php';

$db = DatabaseConnector::getInstance();
$pdo = $db->getConnection();

try {
    // Check if filtering by patient_id or doctor_id
    $patientId = isset($_GET['patient_id']) ? (int)$_GET['patient_id'] : null;
    $doctorId = isset($_GET['doctor_id']) ? (int)$_GET['doctor_id'] : null;
    
    $sql = "
        SELECT 
            a.id,
            a.patient_id,
            a.staff_id,
            a.appointment_time,
            a.department,
            a.status,
            a.notes,
            p.first_name as patient_first_name,
            p.last_name as patient_last_name,
            CONCAT(p.first_name, ' ', p.last_name) as patient_name,
            s.first_name as doctor_first_name,
            s.last_name as doctor_last_name,
            CONCAT(s.first_name, ' ', s.last_name) as doctor_name
        FROM appointments a
        LEFT JOIN patients p ON a.patient_id = p.id
        LEFT JOIN staff s ON a.staff_id = s.id
    ";
    
    $params = [];
    $conditions = [];
    
    if ($patientId) {
        $conditions[] = "a.patient_id = ?";
        $params[] = $patientId;
    }
    
    if ($doctorId) {
        $conditions[] = "a.staff_id = ?";
        $params[] = $doctorId;
    }
    
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }
    
    $sql .= " ORDER BY a.appointment_time DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $appointments = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'appointments' => $appointments
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
