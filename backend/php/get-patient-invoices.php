<?php
/**
 * Get invoices for a specific patient
 */

header('Content-Type: application/json');

require_once 'DatabaseConnector.php';

$db = DatabaseConnector::getInstance();
$pdo = $db->getConnection();

try {
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
            i.id,
            i.patient_id,
            i.total,
            i.status,
            i.created_at,
            CONCAT(p.first_name, ' ', p.last_name) as patient_name
        FROM invoices i
        JOIN patients p ON i.patient_id = p.id
        WHERE i.patient_id = ?
        ORDER BY i.created_at DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$patientId]);
    $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'invoices' => $invoices
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
