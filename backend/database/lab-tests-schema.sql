-- Lab Tests Table
CREATE TABLE IF NOT EXISTS lab_tests (
  id INT AUTO_INCREMENT PRIMARY KEY,
  patient_id INT NOT NULL,
  test_type VARCHAR(255) NOT NULL,
  test_description TEXT,
  technician_id INT NOT NULL,
  results TEXT,
  notes TEXT,
  status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  reviewed_by INT NULL,
  reviewed_at TIMESTAMP NULL,
  rejection_reason TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
  FOREIGN KEY (technician_id) REFERENCES staff(id) ON DELETE CASCADE,
  FOREIGN KEY (reviewed_by) REFERENCES admins(id) ON DELETE SET NULL
);

-- Lab Test Types Configuration Table
CREATE TABLE IF NOT EXISTS lab_test_types (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL UNIQUE,
  description TEXT,
  is_active BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default test types
INSERT INTO lab_test_types (name, description) VALUES
('Blood Test', 'Complete blood count and analysis'),
('Urine Analysis', 'Urinalysis and culture tests'),
('X-Ray', 'Radiographic imaging'),
('CT Scan', 'Computed tomography scan'),
('MRI', 'Magnetic resonance imaging'),
('Ultrasound', 'Sonography examination')
ON DUPLICATE KEY UPDATE name=name;
