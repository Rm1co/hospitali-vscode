<?php
/**
 * Patient creates an appointment request
 */

header('Content-Type: application/json');

require_once 'DatabaseConnector.php';

$db = DatabaseConnector::getInstance();
$pdo = $db->getConnection();

try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['patient_id']) || !isset($input['department']) || 
        !isset($input['preferred_date']) || !isset($input['preferred_time']) ||
        !isset($input['reason'])) {
        throw new Exception('Missing required fields');
    }

    $patientId = (int)$input['patient_id'];
    $department = $input['department'];
    $preferredDate = $input['preferred_date'];
    $preferredTime = $input['preferred_time'];
    $reason = $input['reason'];

    // Verify patient exists
    $patientCheck = $db->fetchOne(
        'SELECT id FROM patients WHERE id = ?',
        [$patientId]
    );

    if (!$patientCheck) {
        throw new Exception('Patient not found');
    }

    // Insert appointment request
    $requestId = $db->insert('appointment_requests', [
        'patient_id' => $patientId,
        'department' => $department,
        'preferred_date' => $preferredDate,
        'preferred_time' => $preferredTime,
        'reason' => $reason,
        'status' => 'Pending'
    ]);

    echo json_encode([
        'success' => true,
        'request_id' => $requestId,
        'message' => 'Appointment request submitted successfully'
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
