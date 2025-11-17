<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'DatabaseConnector.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $data = json_decode(file_get_contents('php://input'), true);
  
  $invoiceId = $data['invoice_id'] ?? null;
  $patientId = $data['patient_id'] ?? null;
  $amount = $data['amount'] ?? null;
  $paymentMethod = $data['payment_method'] ?? null;
  
  if (!$invoiceId || !$patientId || !$amount || !$paymentMethod) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
  }
  
  try {
    $db = DatabaseConnector::getInstance();
    $pdo = $db->getConnection();
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Verify invoice belongs to patient and is unpaid
    $stmt = $pdo->prepare("SELECT * FROM invoices WHERE id = ? AND patient_id = ? AND status = 'unpaid'");
    $stmt->execute([$invoiceId, $patientId]);
    $invoice = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$invoice) {
      $pdo->rollBack();
      echo json_encode(['success' => false, 'message' => 'Invoice not found or already paid']);
      exit;
    }
    
    // Verify amount matches
    if (abs(floatval($invoice['total']) - floatval($amount)) > 0.01) {
      $pdo->rollBack();
      echo json_encode(['success' => false, 'message' => 'Payment amount does not match invoice total']);
      exit;
    }
    
    // Insert payment record
    $transactionId = 'TXN' . strtoupper(uniqid());
    $stmt = $pdo->prepare("
      INSERT INTO payments (
        invoice_id, 
        patient_id, 
        amount, 
        payment_method, 
        transaction_id,
        mpesa_phone,
        card_last_four,
        insurance_provider,
        insurance_policy,
        status,
        payment_date
      ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'completed', NOW())
    ");
    
    $mpesaPhone = $paymentMethod === 'mpesa' ? $data['mpesa_phone'] : null;
    $cardLastFour = $paymentMethod === 'card' ? substr(str_replace(' ', '', $data['card_number']), -4) : null;
    $insuranceProvider = $paymentMethod === 'insurance' ? $data['insurance_provider'] : null;
    $insurancePolicy = $paymentMethod === 'insurance' ? $data['insurance_policy'] : null;
    
    $stmt->execute([
      $invoiceId,
      $patientId,
      $amount,
      $paymentMethod,
      $transactionId,
      $mpesaPhone,
      $cardLastFour,
      $insuranceProvider,
      $insurancePolicy
    ]);
    
    // Update invoice status to paid
    $stmt = $pdo->prepare("UPDATE invoices SET status = 'paid' WHERE id = ?");
    $stmt->execute([$invoiceId]);
    
    // Commit transaction
    $pdo->commit();
    
    echo json_encode([
      'success' => true,
      'message' => 'Payment processed successfully',
      'transaction_id' => $transactionId
    ]);
    
  } catch (Exception $e) {
    if ($pdo->inTransaction()) {
      $pdo->rollBack();
    }
    echo json_encode([
      'success' => false,
      'message' => 'Database error: ' . $e->getMessage()
    ]);
  }
} else {
  echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
