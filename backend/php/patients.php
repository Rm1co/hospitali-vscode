<?php
// Patients CRUD endpoint
// POST: create patient
// GET: list patients (with optional filters)
// PUT: update patient (requires id parameter)
// DELETE: delete patient (requires id parameter)

header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

try {
    require_once __DIR__ . '/DatabaseConnector.php';
    $db = DatabaseConnector::getInstance();

    if ($method === 'POST') {
        // Create new patient
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) throw new Exception('Invalid JSON');

        // Validate required fields
        if (empty($input['first_name']) || empty($input['last_name'])) {
            throw new Exception('First name and last name are required');
        }

        $data = [
            'first_name' => $input['first_name'],
            'last_name' => $input['last_name'],
            'dob' => $input['dob'] ?? null,
            'gender' => $input['gender'] ?? null,
            'phone' => $input['phone'] ?? null,
            'address' => $input['address'] ?? null
        ];

        $patientId = $db->insert('patients', $data);
        
        http_response_code(201);
        echo json_encode([
            'id' => (int)$patientId,
            'message' => 'Patient created successfully'
        ]);
        exit;

    } elseif ($method === 'GET') {
        // List patients
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
        $patients = $db->getPatients($limit);
        
        echo json_encode([
            'patients' => $patients,
            'count' => count($patients)
        ]);
        exit;

    } elseif ($method === 'PUT') {
        // Update patient
        $input = json_decode(file_get_contents('php://input'), true);
        if (!isset($_GET['id'])) throw new Exception('Patient ID required');

        $id = (int)$_GET['id'];
        $data = [];
        
        if (isset($input['first_name'])) $data['first_name'] = $input['first_name'];
        if (isset($input['last_name'])) $data['last_name'] = $input['last_name'];
        if (isset($input['dob'])) $data['dob'] = $input['dob'];
        if (isset($input['gender'])) $data['gender'] = $input['gender'];
        if (isset($input['phone'])) $data['phone'] = $input['phone'];
        if (isset($input['address'])) $data['address'] = $input['address'];

        if (empty($data)) throw new Exception('No fields to update');

        $affected = $db->updatePatient($id, $data);
        
        echo json_encode([
            'affected' => $affected,
            'message' => 'Patient updated'
        ]);
        exit;

    } elseif ($method === 'DELETE') {
        // Delete patient
        if (!isset($_GET['id'])) throw new Exception('Patient ID required');

        $id = (int)$_GET['id'];
        $affected = $db->deletePatient($id);
        
        echo json_encode([
            'affected' => $affected,
            'message' => 'Patient deleted'
        ]);
        exit;

    } else {
        http_response_code(405);
        echo json_encode(['message' => 'Method not allowed']);
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['message' => $e->getMessage()]);
}
