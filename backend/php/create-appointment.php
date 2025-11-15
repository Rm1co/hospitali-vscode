<?php

header('Content-Type: application/json');

require_once 'DatabaseConnector.php';

$db = DatabaseConnector::getInstance();
$pdo = $db->getConnection();

try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['patient_id']) || !isset($input['staff_id']) || 
        !isset($input['appointment_time']) || !isset($input['department'])) {
        throw new Exception('Missing required fields');
    }

    $patientId = (int)$input['patient_id'];
    $staffId = (int)$input['staff_id'];
    $appointmentTime = $input['appointment_time'];
    $department = $input['department'];
    $notes = $input['notes'] ?? '';
    $status = $input['status'] ?? 'Scheduled';

    $doctorCheck = $db->fetchOne(
        'SELECT id, role FROM staff WHERE id = ? AND role = "Doctor" AND is_activated = 1',
        [$staffId]
    );

    if (!$doctorCheck) {
        throw new Exception('Doctor not found or inactive');
    }

    $patientCheck = $db->fetchOne(
        'SELECT id FROM patients WHERE id = ?',
        [$patientId]
    );

    if (!$patientCheck) {
        throw new Exception('Patient not found');
    }

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

    // Insert appointment
    $appointmentId = $db->insert('appointments', [
        'patient_id' => $patientId,
        'staff_id' => $staffId,
        'appointment_time' => $appointmentTime,
        'department' => $department,
        'status' => $status,
        'notes' => $notes
    ]);

    echo json_encode([
        'success' => true,
        'appointment_id' => $appointmentId,
        'message' => 'Appointment created successfully'
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
