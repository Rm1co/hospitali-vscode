<?php
require_once 'DatabaseConnector.php';

try {
  $db = DatabaseConnector::getInstance();
  $pdo = $db->getConnection();
  
  $sql = "CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    patient_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    transaction_id VARCHAR(100) UNIQUE,
    mpesa_phone VARCHAR(20),
    card_last_four VARCHAR(4),
    insurance_provider VARCHAR(100),
    insurance_policy VARCHAR(100),
    status VARCHAR(20) DEFAULT 'completed',
    payment_date DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    INDEX idx_invoice_id (invoice_id),
    INDEX idx_patient_id (patient_id),
    INDEX idx_transaction_id (transaction_id)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
  
  $pdo->exec($sql);
  echo "Payments table created successfully!\n";
  
} catch (Exception $e) {
  echo "Error: " . $e->getMessage() . "\n";
}
