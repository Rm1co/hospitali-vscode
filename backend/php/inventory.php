<?php
header('Content-Type: application/json');
require_once 'DatabaseConnector.php';

$db = DatabaseConnector::getInstance();

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($method === 'GET' && $action === 'list') {
    // Fetch all inventory items
    try {
        $items = $db->fetchAll('SELECT * FROM inventory ORDER BY id DESC');
        http_response_code(200);
        echo json_encode(['success' => true, 'data' => $items]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
elseif ($method === 'POST' && $action === 'add') {
    // Add new inventory item
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['name']) || !isset($input['quantity']) || !isset($input['unit'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Missing required fields']);
            exit;
        }

        $result = $db->insert('inventory', [
            'name' => $input['name'],
            'quantity' => (int)$input['quantity'],
            'unit' => $input['unit']
        ]);

        http_response_code(201);
        echo json_encode(['success' => true, 'id' => $result, 'message' => 'Item added successfully']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
elseif ($method === 'PUT' && $action === 'update') {
    // Update inventory item
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Missing item ID']);
            exit;
        }

        $data = [];
        if (isset($input['name'])) $data['name'] = $input['name'];
        if (isset($input['quantity'])) $data['quantity'] = (int)$input['quantity'];
        if (isset($input['unit'])) $data['unit'] = $input['unit'];

        if (empty($data)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'No fields to update']);
            exit;
        }

        $affected = $db->update('inventory', $data, 'id = ?', [$input['id']]);

        if ($affected > 0) {
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Item updated successfully']);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Item not found']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
elseif ($method === 'DELETE' && $action === 'delete') {
    // Delete inventory item
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Missing item ID']);
            exit;
        }

        $affected = $db->delete('inventory', 'id = ?', [$input['id']]);

        if ($affected > 0) {
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Item deleted successfully']);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Item not found']);
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
