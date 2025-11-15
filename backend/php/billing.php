<?php
header('Content-Type: application/json');
require_once 'DatabaseConnector.php';

$db = DatabaseConnector::getInstance();

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($method === 'GET' && $action === 'list') {
    // Fetch all invoices with patient names
    try {
        $invoices = $db->fetchAll('
            SELECT i.id, i.patient_id, i.total, i.status, i.created_at,
                   CONCAT(p.first_name, " ", p.last_name) as patient_name
            FROM invoices i
            LEFT JOIN patients p ON i.patient_id = p.id
            ORDER BY i.id DESC
        ');
        http_response_code(200);
        echo json_encode(['success' => true, 'data' => $invoices]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
elseif ($method === 'GET' && $action === 'patients') {
    // Fetch all patients for dropdown
    try {
        $patients = $db->fetchAll('SELECT id, first_name, last_name FROM patients ORDER BY first_name, last_name');
        http_response_code(200);
        echo json_encode(['success' => true, 'data' => $patients]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
elseif ($method === 'POST' && $action === 'create') {
    // Create new invoice
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['patient_id']) || !isset($input['total']) || !isset($input['status'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Missing required fields']);
            exit;
        }

        $result = $db->insert('invoices', [
            'patient_id' => (int)$input['patient_id'],
            'total' => (float)$input['total'],
            'status' => $input['status']
        ]);

        http_response_code(201);
        echo json_encode(['success' => true, 'id' => $result, 'message' => 'Invoice created successfully']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
elseif ($method === 'PUT' && $action === 'update') {
    // Update invoice status or amount
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Missing invoice ID']);
            exit;
        }

        $data = [];
        if (isset($input['total'])) $data['total'] = (float)$input['total'];
        if (isset($input['status'])) $data['status'] = $input['status'];

        if (empty($data)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'No fields to update']);
            exit;
        }

        $affected = $db->update('invoices', $data, 'id = ?', [$input['id']]);

        if ($affected > 0) {
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Invoice updated successfully']);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Invoice not found']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
elseif ($method === 'DELETE' && $action === 'delete') {
    // Delete invoice
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Missing invoice ID']);
            exit;
        }

        $affected = $db->delete('invoices', 'id = ?', [$input['id']]);

        if ($affected > 0) {
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Invoice deleted successfully']);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Invoice not found']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
else {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}
?>
