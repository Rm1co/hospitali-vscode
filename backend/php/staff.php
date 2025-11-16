<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'DatabaseConnector.php';

try {
    $db = DatabaseConnector::getInstance();
    $pdo = $db->getConnection();
    
    $action = $_GET['action'] ?? 'list';
    
    switch ($action) {
        case 'list':
            // Get all staff members
            $role = $_GET['role'] ?? null;
            
            $sql = "SELECT s.*, 
                    COUNT(DISTINCT lt.id) as total_tests,
                    COUNT(DISTINCT CASE WHEN lt.status = 'approved' THEN lt.id END) as approved_tests
                    FROM staff s
                    LEFT JOIN lab_tests lt ON s.id = lt.technician_id";
            
            if ($role) {
                $sql .= " WHERE s.role = :role";
            }
            
            $sql .= " GROUP BY s.id ORDER BY s.first_name ASC, s.last_name ASC";
            
            $stmt = $pdo->prepare($sql);
            if ($role) {
                $stmt->bindParam(':role', $role, PDO::PARAM_STR);
            }
            $stmt->execute();
            $staff = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calculate approval rate for each staff member
            foreach ($staff as &$member) {
                $member['approval_rate'] = $member['total_tests'] > 0 
                    ? round(($member['approved_tests'] / $member['total_tests']) * 100, 1)
                    : 0;
            }
            
            echo json_encode([
                'success' => true,
                'data' => $staff
            ]);
            break;
            
        case 'get':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                throw new Exception('Staff ID is required');
            }
            
            $stmt = $pdo->prepare("SELECT * FROM staff WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $staff = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$staff) {
                throw new Exception('Staff member not found');
            }
            
            echo json_encode([
                'success' => true,
                'data' => $staff
            ]);
            break;
            
        case 'lab_technicians':
            // Get basic staff info
            $stmt = $pdo->prepare("SELECT id, first_name, last_name, email, phone, role,
                                   CONCAT(first_name, ' ', last_name) as full_name
                                   FROM staff 
                                   WHERE role = ? 
                                   ORDER BY first_name ASC, last_name ASC");
            $stmt->execute(['Lab Technician']);
            $technicians = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Add stats for each technician
            foreach ($technicians as &$tech) {
                // Set defaults
                $tech['total_tests'] = 0;
                $tech['approved_tests'] = 0;
                $tech['pending_tests'] = 0;
                $tech['rejected_tests'] = 0;
                $tech['approval_rate'] = 0;
                
                // Try to get stats from lab_tests if table exists
                try {
                    $statsStmt = $pdo->prepare("SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
                        FROM lab_tests WHERE technician_id = ?");
                    $statsStmt->execute([$tech['id']]);
                    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($stats) {
                        $tech['total_tests'] = (int)$stats['total'];
                        $tech['approved_tests'] = (int)$stats['approved'];
                        $tech['pending_tests'] = (int)$stats['pending'];
                        $tech['rejected_tests'] = (int)$stats['rejected'];
                        $tech['approval_rate'] = $tech['total_tests'] > 0 
                            ? round(($tech['approved_tests'] / $tech['total_tests']) * 100, 1)
                            : 0;
                    }
                } catch (PDOException $e) {
                    // Table doesn't exist or error, keep defaults
                }
            }
            
            echo json_encode([
                'success' => true,
                'data' => $technicians
            ]);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    error_log("Staff API Error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
