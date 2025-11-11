<?php
// Login endpoint: authenticate patient against patient_accounts table
// Expects JSON body: { email, password }
// Returns JSON with patient info and sets session

session_start();
header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || !isset($input['email']) || !isset($input['password'])) {
        throw new Exception('Missing email or password');
    }

    $email = filter_var($input['email'], FILTER_VALIDATE_EMAIL);
    if (!$email) throw new Exception('Invalid email');

    require_once __DIR__ . '/DatabaseConnector.php';
    $db = DatabaseConnector::getInstance();

    // Find account by email
    $account = $db->fetchOne(
        'SELECT id, patient_id, password_hash FROM patient_accounts WHERE email = ?',
        [$email]
    );
    if (!$account) {
        http_response_code(401);
        echo json_encode(['message' => 'Invalid credentials']);
        exit;
    }

    // Verify password
    if (!password_verify($input['password'], $account['password_hash'])) {
        http_response_code(401);
        echo json_encode(['message' => 'Invalid credentials']);
        exit;
    }

    // Fetch patient info if linked
    $patient = null;
    if ($account['patient_id']) {
        $patient = $db->getPatientById($account['patient_id']);
    }

    // Set session
    $_SESSION['account_id'] = $account['id'];
    $_SESSION['patient_id'] = $account['patient_id'];
    $_SESSION['email'] = $email;

    echo json_encode([
        'account_id' => (int)$account['id'],
        'patient_id' => $account['patient_id'] ? (int)$account['patient_id'] : null,
        'email' => $email,
        'patient' => $patient
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['message' => $e->getMessage()]);
}
