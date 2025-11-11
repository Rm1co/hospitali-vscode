-- Hospital Management System Database Schema
-- Created: November 2025
-- Database: hospital_management_system

-- ============================================
-- 1. PATIENTS TABLE
-- ============================================
CREATE TABLE patients (
  id INT AUTO_INCREMENT PRIMARY KEY,
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  dob DATE,
  gender CHAR(1) COMMENT 'M=Male, F=Female, O=Other',
  phone VARCHAR(30),
  address TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_name (first_name, last_name),
  INDEX idx_phone (phone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 2. PATIENT ACCOUNTS TABLE (Login/Auth)
-- ============================================
CREATE TABLE patient_accounts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  patient_id INT,
  email VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  is_active BOOLEAN DEFAULT TRUE,
  last_login TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
  INDEX idx_email (email),
  INDEX idx_patient_id (patient_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 3. STAFF TABLE
-- ============================================
CREATE TABLE staff (
  id INT AUTO_INCREMENT PRIMARY KEY,
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  role VARCHAR(100) NOT NULL COMMENT 'Doctor, Nurse, Admin, etc.',
  department VARCHAR(100),
  phone VARCHAR(30),
  email VARCHAR(255),
  license_number VARCHAR(100),
  is_active BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_name (first_name, last_name),
  INDEX idx_role (role),
  INDEX idx_department (department),
  INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 4. APPOINTMENTS TABLE
-- ============================================
CREATE TABLE appointments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  patient_id INT NOT NULL,
  staff_id INT,
  appointment_time DATETIME NOT NULL,
  department VARCHAR(100),
  status VARCHAR(50) DEFAULT 'Scheduled' COMMENT 'Scheduled, Confirmed, Completed, Cancelled, No-show',
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
  FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE SET NULL,
  INDEX idx_patient_id (patient_id),
  INDEX idx_staff_id (staff_id),
  INDEX idx_appointment_time (appointment_time),
  INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 5. INVENTORY TABLE
-- ============================================
CREATE TABLE inventory (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  category VARCHAR(100),
  description TEXT,
  quantity INT DEFAULT 0,
  minimum_stock INT DEFAULT 10,
  unit VARCHAR(50) COMMENT 'tablets, bottles, units, boxes, etc.',
  unit_price DECIMAL(10, 2),
  supplier VARCHAR(255),
  expiry_date DATE,
  is_active BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_name (name),
  INDEX idx_category (category),
  INDEX idx_quantity (quantity),
  INDEX idx_expiry_date (expiry_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 6. INVOICES TABLE (Billing)
-- ============================================
CREATE TABLE invoices (
  id INT AUTO_INCREMENT PRIMARY KEY,
  patient_id INT NOT NULL,
  invoice_number VARCHAR(50) UNIQUE,
  total DECIMAL(10, 2) NOT NULL,
  paid_amount DECIMAL(10, 2) DEFAULT 0,
  status VARCHAR(50) DEFAULT 'unpaid' COMMENT 'unpaid, partially_paid, paid, overdue, cancelled',
  due_date DATE,
  payment_method VARCHAR(100) COMMENT 'cash, card, insurance, etc.',
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
  INDEX idx_patient_id (patient_id),
  INDEX idx_status (status),
  INDEX idx_invoice_number (invoice_number),
  INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 7. INVOICE ITEMS TABLE (Line items for invoices)
-- ============================================
CREATE TABLE invoice_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  invoice_id INT NOT NULL,
  description VARCHAR(255),
  quantity INT,
  unit_price DECIMAL(10, 2),
  total DECIMAL(10, 2),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
  INDEX idx_invoice_id (invoice_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 8. MEDICAL RECORDS TABLE
-- ============================================
CREATE TABLE medical_records (
  id INT AUTO_INCREMENT PRIMARY KEY,
  patient_id INT NOT NULL,
  staff_id INT,
  visit_date DATETIME DEFAULT CURRENT_TIMESTAMP,
  diagnosis TEXT,
  treatment TEXT,
  medications TEXT,
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
  FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE SET NULL,
  INDEX idx_patient_id (patient_id),
  INDEX idx_visit_date (visit_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 9. AUDIT LOG TABLE (Track changes)
-- ============================================
CREATE TABLE audit_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  table_name VARCHAR(100),
  record_id INT,
  action VARCHAR(50) COMMENT 'INSERT, UPDATE, DELETE',
  changed_fields JSON,
  user_id INT,
  user_email VARCHAR(255),
  ip_address VARCHAR(45),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_table_name (table_name),
  INDEX idx_record_id (record_id),
  INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 10. DEPARTMENTS TABLE
-- ============================================
CREATE TABLE departments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  head_staff_id INT,
  phone VARCHAR(30),
  location VARCHAR(255),
  is_active BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (head_staff_id) REFERENCES staff(id) ON DELETE SET NULL,
  INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 11. SAMPLE DATA (Optional - for testing)
-- ============================================

-- Sample Departments
INSERT INTO departments (name, phone, location) VALUES
  ('Cardiology', '555-0101', 'Building A, Floor 3'),
  ('Emergency', '555-0102', 'Building A, Ground Floor'),
  ('Pediatrics', '555-0103', 'Building B, Floor 2'),
  ('General Ward', '555-0104', 'Building C, Floors 1-4');

-- Sample Staff
INSERT INTO staff (first_name, last_name, role, department, email, phone) VALUES
  ('Michael', 'Rutto', 'Doctor', 'Cardiology', 'rutto@hospital.com', '555-2001'),
  ('Joyce', 'Mbiriri', 'Nurse', 'General Ward', 'mbiriri@hospital.com', '555-2002'),
  ('Wilson', 'Mutua', 'Doctor', 'Emergency', 'mutua@hospital.com', '555-2003'),
  ('Brian', 'Mathara', 'Doctor', 'Pediatrics', 'mathara@hospital.com', '555-2004');

-- Sample Inventory
INSERT INTO inventory (name, category, quantity, unit, unit_price, minimum_stock) VALUES
  ('Paracetamol 500mg', 'Medication', 120, 'tablets', 0.50, 50),
  ('Saline Solution 500ml', 'Solution', 25, 'bottles', 5.00, 10),
  ('Sterile Syringes', 'Medical Supply', 340, 'pieces', 0.25, 100),
  ('Glucose Strips', 'Diagnostic', 200, 'strips', 1.50, 100);

-- Sample Patient (for testing login)
INSERT INTO patients (first_name, last_name, dob, gender, phone, address) VALUES
  ('Jane', 'Doe', '1990-05-15', 'F', '555-3001', '123 Main St, Tel Aviv');

-- Sample Patient Account (password: TestPass123!)
INSERT INTO patient_accounts (patient_id, email, password_hash) VALUES
  (1, 'jane.doe@test.com', '$2y$10$abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');

-- ============================================
-- FINAL NOTES
-- ============================================
-- 1. Update database credentials in DatabaseConnector.php
-- 2. All tables use InnoDB for transaction support
-- 3. UTF8MB4 for full Unicode support
-- 4. Foreign keys with CASCADE delete where appropriate
-- 5. Indexes on commonly queried fields for performance
-- 6. Timestamps for audit trail
