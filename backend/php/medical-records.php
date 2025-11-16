<?php
/**
 * medical-records.php
 * Handles medical records operations
 */

header('Content-Type: application/json');

require_once 'DatabaseConnector.php';

$db = DatabaseConnector::getInstance();
$pdo = $db->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        // Get medical records for a patient
        if (!isset($_GET['patient_id'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Patient ID is required'
            ]);
            exit;
        }

        $patientId = (int)$_GET['patient_id'];

        $sql = "
            SELECT 
                mr.id,
                mr.patient_id,
                mr.appointment_id,
                mr.staff_id,
                mr.visit_date,
                mr.appointment_date,
                mr.diagnosis,
                mr.treatment,
                mr.medications,
                mr.notes,
                mr.reason,
                mr.created_at,
                CONCAT(s.first_name, ' ', s.last_name) as doctor_name,
                s.role as doctor_role,
                s.department as doctor_department
            FROM medical_records mr
            LEFT JOIN staff s ON mr.staff_id = s.id
            WHERE mr.patient_id = ?
            ORDER BY mr.visit_date DESC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$patientId]);
        $records = $stmt->fetchAll();

        echo json_encode([
            'success' => true,
            'records' => $records
        ]);
    } elseif ($method === 'POST') {
        // Create new medical record
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['patient_id']) || !isset($input['diagnosis'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Patient ID and diagnosis are required'
            ]);
            exit;
        }

        $data = [
            'patient_id' => (int)$input['patient_id'],
            'appointment_id' => isset($input['appointment_id']) ? (int)$input['appointment_id'] : null,
            'staff_id' => isset($input['staff_id']) ? (int)$input['staff_id'] : null,
            'diagnosis' => $input['diagnosis'],
            'treatment' => $input['treatment'] ?? null,
            'medications' => $input['medications'] ?? null,
            'notes' => $input['notes'] ?? null,
            'reason' => $input['reason'] ?? null
        ];

        if (isset($input['visit_date'])) {
            $data['visit_date'] = $input['visit_date'];
        }
        
        if (isset($input['appointment_date'])) {
            $data['appointment_date'] = $input['appointment_date'];
        }

        $id = $db->insert('medical_records', $data);

        echo json_encode([
            'success' => true,
            'message' => 'Medical record created successfully',
            'id' => $id
        ]);
    } else {
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'message' => 'Method not allowed'
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
