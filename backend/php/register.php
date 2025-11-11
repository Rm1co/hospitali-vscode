<?php
// Simple registration endpoint. Expects JSON body with fields:
// first_name, last_name, email, password, dob, phone, address

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) throw new Exception('Invalid JSON');

    $required = ['first_name', 'last_name', 'email', 'password'];
    foreach ($required as $r) {
        if (empty($input[$r])) throw new Exception("Missing: {$r}");
    }

    $email = filter_var($input['email'], FILTER_VALIDATE_EMAIL);
    if (!$email) throw new Exception('Invalid email');

    // sanitize/prepare patient data
    $patientData = [
        'first_name' => $input['first_name'],
        'last_name' => $input['last_name'],
        'dob' => $input['dob'] ?? null,
        'gender' => $input['gender'] ?? null,
        'phone' => $input['phone'] ?? null,
        'address' => $input['address'] ?? null
    ];

    require_once __DIR__ . '/DatabaseConnector.php';
    $db = DatabaseConnector::getInstance();

    // Check if email already exists in accounts
    $exists = $db->fetchOne('SELECT id FROM patient_accounts WHERE email = ?', [$email]);
    if ($exists) {
        http_response_code(409);
        echo json_encode(['message' => 'Email already registered']);
        exit;
    }

    // Check if patient with same name+dob exists to avoid duplicates
    $duplicate = null;
    if ($input['dob']) {
        $duplicate = $db->fetchOne(
            'SELECT id FROM patients WHERE first_name = ? AND last_name = ? AND dob = ?',
            [$input['first_name'], $input['last_name'], $input['dob']]
        );
    }

    // Insert patient record if not a duplicate, otherwise reuse existing
    $db->beginTransaction();
    try {
        if ($duplicate) {
            $patientId = $duplicate['id'];
        } else {
            $patientId = $db->insert('patients', $patientData);
        }

        // Insert account
        $passwordHash = password_hash($input['password'], PASSWORD_DEFAULT);
        $accountId = $db->insert('patient_accounts', [
            'patient_id' => $patientId,
            'email' => $email,
            'password_hash' => $passwordHash
        ]);

        $db->commit();

        echo json_encode(['id' => (int)$accountId]);
        exit;
    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['message' => $e->getMessage()]);
}
