<?php
/**
 * Secretary schedules appointment from a request
 */

header('Content-Type: application/json');

require_once 'DatabaseConnector.php';

$db = DatabaseConnector::getInstance();
$pdo = $db->getConnection();

try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['request_id']) || !isset($input['staff_id']) || 
        !isset($input['appointment_time'])) {
        throw new Exception('Missing required fields');
    }

    $requestId = (int)$input['request_id'];
    $staffId = (int)$input['staff_id'];
    $appointmentTime = $input['appointment_time'];
    $notes = $input['notes'] ?? '';

    // Get the request details
    $request = $db->fetchOne(
        'SELECT * FROM appointment_requests WHERE id = ? AND status = "Pending"',
        [$requestId]
    );

    if (!$request) {
        throw new Exception('Request not found or already processed');
    }

    // Verify doctor exists and is activated
    $doctorCheck = $db->fetchOne(
        'SELECT id, role, department FROM staff WHERE id = ? AND role = "Doctor" AND is_activated = 1',
        [$staffId]
    );

    if (!$doctorCheck) {
        throw new Exception('Doctor not found or inactive');
    }

    // Check for conflicting appointments
    $conflictCheck = $db->fetchOne(
        "SELECT id FROM appointments 
         WHERE staff_id = ? 
         AND appointment_time BETWEEN DATE_SUB(?, INTERVAL 15 MINUTE) AND DATE_ADD(?, INTERVAL 15 MINUTE)
         AND status NOT IN ('Cancelled', 'No-show')",
        [$staffId, $appointmentTime, $appointmentTime]
    );

    if ($conflictCheck) {
        throw new Exception('Doctor has a conflicting appointment at this time');
    }

    // Create the appointment
    $appointmentId = $db->insert('appointments', [
        'patient_id' => $request['patient_id'],
        'staff_id' => $staffId,
        'appointment_time' => $appointmentTime,
        'department' => $request['department'],
        'status' => 'Scheduled',
        'notes' => $notes . "\n\nReason: " . $request['reason']
    ]);

    // Update request status
    $db->update('appointment_requests', ['status' => 'Scheduled'], 'id = ?', [$requestId]);

    echo json_encode([
        'success' => true,
        'appointment_id' => $appointmentId,
        'message' => 'Appointment scheduled successfully'
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
