<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'DatabaseConnector.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $data = json_decode(file_get_contents('php://input'), true);
  
  $patientId = $data['id'] ?? null;
  $firstName = $data['first_name'] ?? null;
  $lastName = $data['last_name'] ?? null;
  $phone = $data['phone'] ?? null;
  $dob = $data['dob'] ?? null;
  $gender = $data['gender'] ?? null;
  $address = $data['address'] ?? null;
  
  if (!$patientId || !$firstName || !$lastName) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
  }
  
  try {
    $db = DatabaseConnector::getInstance();
    
    // Prepare data for update
    $updateData = [
      'first_name' => $firstName,
      'last_name' => $lastName
    ];
    
    // Add optional fields only if they have values
    if ($phone !== null && $phone !== '') {
      $updateData['phone'] = $phone;
    }
    if ($dob !== null && $dob !== '') {
      $updateData['dob'] = $dob;
    }
    if ($gender !== null && $gender !== '') {
      $updateData['gender'] = $gender;
    }
    if ($address !== null && $address !== '') {
      $updateData['address'] = $address;
    }
    
    // Update patient profile using DatabaseConnector's update method
    $affectedRows = $db->update('patients', $updateData, 'id = ?', [$patientId]);
    
    if ($affectedRows >= 0) {
      echo json_encode([
        'success' => true,
        'message' => 'Profile updated successfully',
        'affected_rows' => $affectedRows
      ]);
    } else {
      echo json_encode([
        'success' => false,
        'message' => 'Failed to update profile'
      ]);
    }
    
  } catch (Exception $e) {
    echo json_encode([
      'success' => false,
      'message' => 'Database error: ' . $e->getMessage()
    ]);
  }
} else {
  echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
