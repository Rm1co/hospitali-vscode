<?php
require_once 'DatabaseConnector.php';

$db = DatabaseConnector::getInstance();
$conn = $db->getConnection();

// First, let's get a lab technician ID
$techQuery = "SELECT id FROM staff WHERE role = 'Lab Technician' LIMIT 1";
$techResult = $conn->query($techQuery)->fetch();

if ($techResult) {
    $techId = $techResult['id'];
    
    // Get some patient IDs
    $patientQuery = "SELECT id FROM patients LIMIT 3";
    $patientResult = $conn->query($patientQuery)->fetchAll();
    
    if ($patientResult && count($patientResult) > 0) {
        $patients = [];
        foreach ($patientResult as $row) {
            $patients[] = $row['id'];
        }
        
        // Insert sample test data
        $tests = [
            [
                'patient_id' => $patients[0],
                'test_type' => 'Blood Test',
                'results' => 'Hemoglobin: 14.5 g/dL (Normal), WBC: 7,200/μL (Normal), Platelets: 250,000/μL (Normal)',
                'notes' => 'All values within normal range. Patient is healthy.',
                'status' => 'pending'
            ],
            [
                'patient_id' => $patients[1] ?? $patients[0],
                'test_type' => 'Urine Analysis',
                'results' => 'pH: 6.5, Specific Gravity: 1.020, Protein: Negative, Glucose: Negative',
                'notes' => 'Normal urine analysis. No abnormalities detected.',
                'status' => 'pending'
            ],
            [
                'patient_id' => $patients[2] ?? $patients[0],
                'test_type' => 'X-Ray',
                'results' => 'Chest X-Ray shows clear lung fields. No signs of infection or abnormality.',
                'notes' => 'Recommended follow-up in 6 months if symptoms persist.',
                'status' => 'approved'
            ]
        ];
        
        foreach ($tests as $test) {
            $stmt = $conn->prepare("INSERT INTO lab_tests (patient_id, test_type, results, notes, technician_id, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$test['patient_id'], $test['test_type'], $test['results'], $test['notes'], $techId, $test['status']]);
            
            echo "✓ Sample test added: " . $test['test_type'] . "\n";
        }
        
        echo "\nSample data added successfully!\n";
    } else {
        echo "No patients found in database.\n";
    }
} else {
    echo "No Lab Technician found in database. Please add one first.\n";
}
?>
