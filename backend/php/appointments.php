<?php
// Appointments endpoint
// GET: list appointments, supports ?patient_id=ID

header('Content-Type: application/json');

try {
    require_once __DIR__ . '/DatabaseConnector.php';
    $db = DatabaseConnector::getInstance();

    $method = $_SERVER['REQUEST_METHOD'];

    if ($method !== 'GET') {
        http_response_code(405);
        echo json_encode(['message' => 'Method not allowed']);
        exit;
    }

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

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => $e->getMessage()]);
}
