<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

require_once 'DatabaseConnector.php';

try {
    $db = DatabaseConnector::getInstance();
    $conn = $db->getConnection();

    $action = $_GET['action'] ?? $_POST['action'] ?? '';

    switch ($action) {
        case 'list':
            listLabTests($conn);
            break;
        case 'pending':
            getPendingTests($conn);
            break;
        case 'submit':
            submitTestResults($conn);
            break;
        case 'approve':
            approveTest($conn);
            break;
        case 'reject':
            rejectTest($conn);
            break;
        case 'update':
            updateTest($conn);
            break;
        case 'delete':
            deleteTest($conn);
            break;
        case 'assigned':
            getAssignedTests($conn);
            break;
        case 'submitted':
            getSubmittedTests($conn);
            break;
        case 'test-types':
            getTestTypes($conn);
            break;
        case 'stats':
            getLabStats($conn);
            break;
        case 'add-test-type':
            addTestType($conn);
            break;
        case 'update-test-type':
            updateTestType($conn);
            break;
        case 'delete-test-type':
            deleteTestType($conn);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

function listLabTests($conn) {
    $query = "SELECT lt.*, 
                     CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                     CONCAT(s.first_name, ' ', s.last_name) as technician_name,
                     CONCAT(a.full_name) as reviewer_name
              FROM lab_tests lt
              LEFT JOIN patients p ON lt.patient_id = p.id
              LEFT JOIN staff s ON lt.technician_id = s.id
              LEFT JOIN admins a ON lt.reviewed_by = a.id
              ORDER BY lt.created_at DESC";
    
    $stmt = $conn->query($query);
    $tests = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'data' => $tests]);
}

function getPendingTests($conn) {
    $query = "SELECT lt.*, 
                     CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                     p.dob as patient_dob,
                     CONCAT(s.first_name, ' ', s.last_name) as technician_name
              FROM lab_tests lt
              LEFT JOIN patients p ON lt.patient_id = p.id
              LEFT JOIN staff s ON lt.technician_id = s.id
              WHERE lt.status = 'pending'
              ORDER BY lt.submitted_at DESC";
    
    $stmt = $conn->query($query);
    $tests = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'data' => $tests]);
}

function submitTestResults($conn) {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $patient_id = $data['patient_id'] ?? null;
        $test_type = $data['test_type'] ?? '';
        $results = $data['results'] ?? '';
        $notes = $data['notes'] ?? '';
        $technician_id = $data['technician_id'] ?? null;
        
        if (!$patient_id || !$test_type || !$results || !$technician_id) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            return;
        }
        
        $stmt = $conn->prepare("INSERT INTO lab_tests (patient_id, test_type, results, notes, technician_id, status) 
                               VALUES (?, ?, ?, ?, ?, 'pending')");
        $stmt->execute([$patient_id, $test_type, $results, $notes, $technician_id]);
        
        echo json_encode(['success' => true, 'message' => 'Test results submitted successfully', 'test_id' => $conn->lastInsertId()]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function approveTest($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $test_id = $data['test_id'] ?? null;
    $reviewer_id = $data['reviewer_id'] ?? null;
    
    if (!$test_id || !$reviewer_id) {
        echo json_encode(['success' => false, 'message' => 'Missing test ID or reviewer ID']);
        return;
    }
    
    $stmt = $conn->prepare("UPDATE lab_tests 
                           SET status = 'approved', reviewed_by = ?, reviewed_at = NOW() 
                           WHERE id = ?");
    $stmt->execute([$reviewer_id, $test_id]);
    
    echo json_encode(['success' => true, 'message' => 'Test approved successfully']);
}

function rejectTest($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $test_id = $data['test_id'] ?? null;
    $reviewer_id = $data['reviewer_id'] ?? null;
    $rejection_reason = $data['rejection_reason'] ?? '';
    
    if (!$test_id || !$reviewer_id) {
        echo json_encode(['success' => false, 'message' => 'Missing test ID or reviewer ID']);
        return;
    }
    
    $stmt = $conn->prepare("UPDATE lab_tests 
                           SET status = 'rejected', reviewed_by = ?, reviewed_at = NOW(), rejection_reason = ? 
                           WHERE id = ?");
    $stmt->execute([$reviewer_id, $rejection_reason, $test_id]);
    
    echo json_encode(['success' => true, 'message' => 'Test rejected successfully']);
}

function updateTest($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $test_id = $data['test_id'] ?? null;
    $results = $data['results'] ?? '';
    $notes = $data['notes'] ?? '';
    
    if (!$test_id) {
        echo json_encode(['success' => false, 'message' => 'Missing test ID']);
        return;
    }
    
    $stmt = $conn->prepare("UPDATE lab_tests SET results = ?, notes = ? WHERE id = ?");
    $stmt->execute([$results, $notes, $test_id]);
    
    echo json_encode(['success' => true, 'message' => 'Test updated successfully']);
}

function deleteTest($conn) {
    $test_id = $_GET['test_id'] ?? null;
    
    if (!$test_id) {
        echo json_encode(['success' => false, 'message' => 'Missing test ID']);
        return;
    }
    
    $stmt = $conn->prepare("DELETE FROM lab_tests WHERE id = ?");
    $stmt->execute([$test_id]);
    
    echo json_encode(['success' => true, 'message' => 'Test deleted successfully']);
}

function getAssignedTests($conn) {
    $technician_id = $_GET['technician_id'] ?? null;
    
    if (!$technician_id) {
        echo json_encode(['success' => false, 'message' => 'Missing technician ID']);
        return;
    }
    
    // For now, return empty array as we need appointment integration
    // In future: fetch tests assigned through appointments
    echo json_encode(['success' => true, 'data' => []]);
}

function getSubmittedTests($conn) {
    $technician_id = $_GET['technician_id'] ?? null;
    
    if (!$technician_id) {
        echo json_encode(['success' => false, 'message' => 'Missing technician ID']);
        return;
    }
    
    $query = "SELECT lt.*, 
                     CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                     CONCAT(a.full_name) as reviewer_name
              FROM lab_tests lt
              LEFT JOIN patients p ON lt.patient_id = p.id
              LEFT JOIN admins a ON lt.reviewed_by = a.id
              WHERE lt.technician_id = ?
              ORDER BY lt.submitted_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([$technician_id]);
    $tests = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'data' => $tests]);
}

function getTestTypes($conn) {
    $query = "SELECT * FROM lab_test_types WHERE is_active = 1 ORDER BY name";
    $stmt = $conn->query($query);
    $types = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'data' => $types]);
}

function getLabStats($conn) {
    // Get counts for dashboard
    $pending_query = "SELECT COUNT(*) as count FROM lab_tests WHERE status = 'pending'";
    $today_query = "SELECT COUNT(*) as count FROM lab_tests WHERE DATE(submitted_at) = CURDATE()";
    $technicians_query = "SELECT COUNT(*) as count FROM staff WHERE role = 'Lab Technician'";
    $month_query = "SELECT COUNT(*) as count FROM lab_tests WHERE MONTH(submitted_at) = MONTH(CURDATE()) AND YEAR(submitted_at) = YEAR(CURDATE())";
    
    $pending = $conn->query($pending_query)->fetch()['count'];
    $today = $conn->query($today_query)->fetch()['count'];
    $technicians = $conn->query($technicians_query)->fetch()['count'];
    $month = $conn->query($month_query)->fetch()['count'];
    
    echo json_encode([
        'success' => true,
        'data' => [
            'pending' => $pending,
            'today' => $today,
            'technicians' => $technicians,
            'month' => $month
        ]
    ]);
}

function addTestType($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $name = $data['name'] ?? '';
    $description = $data['description'] ?? '';
    
    if (empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Test type name is required']);
        return;
    }
    
    // Check if test type already exists
    $checkQuery = "SELECT id FROM lab_test_types WHERE name = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->execute([$name]);
    
    if ($checkStmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Test type already exists']);
        return;
    }
    
    $query = "INSERT INTO lab_test_types (name, description, is_active) VALUES (?, ?, 1)";
    $stmt = $conn->prepare($query);
    $stmt->execute([$name, $description]);
    
    echo json_encode(['success' => true, 'message' => 'Test type added successfully', 'id' => $conn->lastInsertId()]);
}

function updateTestType($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $id = $data['id'] ?? null;
    $name = $data['name'] ?? '';
    $description = $data['description'] ?? '';
    
    if (empty($id) || empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Test type ID and name are required']);
        return;
    }
    
    // Check if test type exists
    $checkQuery = "SELECT id FROM lab_test_types WHERE id = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->execute([$id]);
    
    if (!$checkStmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Test type not found']);
        return;
    }
    
    $query = "UPDATE lab_test_types SET name = ?, description = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$name, $description, $id]);
    
    echo json_encode(['success' => true, 'message' => 'Test type updated successfully']);
}

function deleteTestType($conn) {
    $id = $_GET['id'] ?? null;
    
    if (empty($id)) {
        echo json_encode(['success' => false, 'message' => 'Test type ID is required']);
        return;
    }
    
    // Check if test type is being used
    $checkQuery = "SELECT COUNT(*) as count FROM lab_tests WHERE test_type = (SELECT name FROM lab_test_types WHERE id = ?)";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->execute([$id]);
    $result = $checkStmt->fetch();
    
    if ($result['count'] > 0) {
        echo json_encode(['success' => false, 'message' => 'Cannot delete test type that is being used in existing tests. Consider deactivating it instead.']);
        return;
    }
    
    $query = "DELETE FROM lab_test_types WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$id]);
    
    echo json_encode(['success' => true, 'message' => 'Test type deleted successfully']);
}
?>
