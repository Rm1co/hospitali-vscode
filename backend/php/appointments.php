<?php
// Appointments endpoint
// GET: list appointments, supports ?patient_id=ID
// PUT: update appointment status

header('Content-Type: application/json');

try {
    require_once __DIR__ . '/DatabaseConnector.php';
    $db = DatabaseConnector::getInstance();

    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        if (isset($_GET['patient_id'])) {
            $patientId = (int)$_GET['patient_id'];
            $rows = $db->fetchAll('SELECT a.* , s.first_name AS staff_first, s.last_name AS staff_last FROM appointments a LEFT JOIN staff s ON a.staff_id = s.id WHERE a.patient_id = ? ORDER BY a.appointment_time DESC', [$patientId]);
            echo json_encode(['appointments' => $rows]);
            exit;
        }

        // return recent appointments if no patient specified
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
        $limit = max(1, $limit);
        $rows = $db->fetchAll("SELECT a.* , s.first_name AS staff_first, s.last_name AS staff_last FROM appointments a LEFT JOIN staff s ON a.staff_id = s.id ORDER BY a.appointment_time DESC LIMIT {$limit}");
        echo json_encode(['appointments' => $rows]);
        exit;
    }

    if ($method === 'PUT') {
        // Update appointment status
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['id']) || !isset($input['status'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing appointment ID or status']);
            exit;
        }

        $appointmentId = (int)$input['id'];
        $status = $input['status'];

        // Validate status
        $validStatuses = ['Scheduled', 'Completed', 'Cancelled'];
        if (!in_array($status, $validStatuses)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid status']);
            exit;
        }

        $affected = $db->update('appointments', ['status' => $status], 'id = ?', [$appointmentId]);

        if ($affected > 0) {
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Appointment status updated successfully']);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Appointment not found']);
        }
        exit;
    }

    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => $e->getMessage()]);
}
