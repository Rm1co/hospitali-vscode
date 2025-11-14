<?php
/**
 * get-doctor-availability.php
 * Returns the next available time slot for a specific doctor
 */

header('Content-Type: application/json');

require_once 'DatabaseConnector.php';

$db = DatabaseConnector::getInstance();
$pdo = $db->getConnection();

try {
    if (!isset($_GET['doctor_id'])) {
        throw new Exception('Doctor ID is required');
    }

    $doctorId = (int)$_GET['doctor_id'];

    // Get doctor's next appointment or next available time
    $sql = "
        SELECT MIN(appointment_time) as next_appointment
        FROM appointments
        WHERE staff_id = ?
            AND appointment_time > NOW()
            AND status NOT IN ('Cancelled', 'No-show')
        ORDER BY appointment_time ASC
        LIMIT 1
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$doctorId]);
    $result = $stmt->fetch();
    
    $nextAvailable = $result['next_appointment'] ?? date('Y-m-d H:i:s', strtotime('+1 hour'));

    echo json_encode([
        'success' => true,
        'nextAvailable' => $nextAvailable,
        'doctor_id' => $doctorId
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
