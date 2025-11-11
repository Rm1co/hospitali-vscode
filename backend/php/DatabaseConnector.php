<?php
/**
 * PDO Database Connector
 * 
 * This class provides a singleton PDO connection to the hospital database.
 * It handles connection initialization, error handling, and provides methods
 * for common database operations.
 */

class DatabaseConnector {
    private static $instance = null;
    private $pdo = null;
    
    // Database configuration
    private $host = '10.51.50.147';
    private $db_name = 'hospital_management_system';
    private $user = 'admin';
    private $pass = '12345678';
    private $charset = 'utf8mb4';
    
    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct() {
        $this->connect();
    }
    
    /**
     * Prevent cloning of the singleton instance
     */
    private function __clone() {}
    
    /**
     * Prevent unserializing of the singleton instance
     */
    public function __wakeup() {}
    
    /**
     * Get singleton instance of DatabaseConnector
     * 
     * @return DatabaseConnector
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Establish PDO connection to the database
     * 
     * @throws PDOException
     * @return void
     */
    private function connect() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset={$this->charset}";
            
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            
            $this->pdo = new PDO($dsn, $this->user, $this->pass, $options);
            
        } catch (PDOException $e) {
            die('Database Connection Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Get the PDO connection instance
     * 
     * @return PDO
     */
    public function getConnection() {
        if ($this->pdo === null) {
            $this->connect();
        }
        return $this->pdo;
    }
    
    /**
     * Execute a prepared statement with parameters
     * 
     * @param string $sql SQL query
     * @param array $params Parameters for prepared statement
     * @return PDOStatement
     */
    public function execute($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception('Query execution failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Fetch a single row from query result
     * 
     * @param string $sql SQL query
     * @param array $params Parameters for prepared statement
     * @return array|false
     */
    public function fetchOne($sql, $params = []) {
        $stmt = $this->execute($sql, $params);
        return $stmt->fetch();
    }
    
    /**
     * Fetch all rows from query result
     * 
     * @param string $sql SQL query
     * @param array $params Parameters for prepared statement
     * @return array
     */
    public function fetchAll($sql, $params = []) {
        $stmt = $this->execute($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Insert a record into the database
     * 
     * @param string $table Table name
     * @param array $data Associative array of column => value
     * @return string Last inserted ID
     */
    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        
        try {
            $this->execute($sql, array_values($data));
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception('Insert failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Update records in the database
     * 
     * @param string $table Table name
     * @param array $data Associative array of column => value
     * @param string $where WHERE clause (e.g., "id = ?")
     * @param array $whereParams Parameters for WHERE clause
     * @return int Number of affected rows
     */
    public function update($table, $data, $where, $whereParams = []) {
        $setClause = implode(', ', array_map(fn($col) => "{$col} = ?", array_keys($data)));
        
        $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        $params = array_merge(array_values($data), $whereParams);
        
        try {
            $stmt = $this->execute($sql, $params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw new Exception('Update failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Delete records from the database
     * 
     * @param string $table Table name
     * @param string $where WHERE clause (e.g., "id = ?")
     * @param array $whereParams Parameters for WHERE clause
     * @return int Number of affected rows
     */
    public function delete($table, $where, $whereParams = []) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        
        try {
            $stmt = $this->execute($sql, $whereParams);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw new Exception('Delete failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Begin a database transaction
     * 
     * @return bool
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    /**
     * Commit a database transaction
     * 
     * @return bool
     */
    public function commit() {
        return $this->pdo->commit();
    }
    
    /**
     * Rollback a database transaction
     * 
     * @return bool
     */
    public function rollback() {
        return $this->pdo->rollback();
    }
    
    /**
     * Close the database connection
     * 
     * @return void
     */
    public function closeConnection() {
        $this->pdo = null;
    }

    /* -----------------------------------------------------------------
     * Patients
     * ----------------------------------------------------------------- */
    public function getPatients($limit = null) {
        $sql = "SELECT * FROM patients ORDER BY created_at DESC";
        if ($limit !== null && is_int($limit)) {
            $sql .= " LIMIT ?";
            return $this->fetchAll($sql, [$limit]);
        }
        return $this->fetchAll($sql);
    }

    public function getPatientById($id) {
        return $this->fetchOne("SELECT * FROM patients WHERE id = ?", [$id]);
    }

    public function createPatient(array $data) {
        return $this->insert('patients', $data);
    }

    public function updatePatient($id, array $data) {
        return $this->update('patients', $data, 'id = ?', [$id]);
    }

    public function deletePatient($id) {
        return $this->delete('patients', 'id = ?', [$id]);
    }

    /* -----------------------------------------------------------------
     * Appointments
     * ----------------------------------------------------------------- */
    public function getAppointments($date = null) {
        if ($date !== null) {
            return $this->fetchAll("SELECT * FROM appointments WHERE DATE(appointment_time) = ? ORDER BY appointment_time", [$date]);
        }
        return $this->fetchAll("SELECT * FROM appointments ORDER BY appointment_time");
    }

    public function getAppointmentById($id) {
        return $this->fetchOne("SELECT * FROM appointments WHERE id = ?", [$id]);
    }

    public function createAppointment(array $data) {
        return $this->insert('appointments', $data);
    }

    public function updateAppointment($id, array $data) {
        return $this->update('appointments', $data, 'id = ?', [$id]);
    }

    public function updateAppointmentStatus($id, $status) {
        return $this->update('appointments', ['status' => $status], 'id = ?', [$id]);
    }

    public function deleteAppointment($id) {
        return $this->delete('appointments', 'id = ?', [$id]);
    }

    /* -----------------------------------------------------------------
     * Staff
     * ----------------------------------------------------------------- */
    public function getStaff() {
        return $this->fetchAll("SELECT * FROM staff ORDER BY created_at DESC");
    }

    public function getStaffById($id) {
        return $this->fetchOne("SELECT * FROM staff WHERE id = ?", [$id]);
    }

    public function createStaff(array $data) {
        return $this->insert('staff', $data);
    }

    public function updateStaff($id, array $data) {
        return $this->update('staff', $data, 'id = ?', [$id]);
    }

    public function deleteStaff($id) {
        return $this->delete('staff', 'id = ?', [$id]);
    }

    /* -----------------------------------------------------------------
     * Inventory
     * ----------------------------------------------------------------- */
    public function getInventoryItems() {
        return $this->fetchAll("SELECT * FROM inventory ORDER BY name");
    }

    public function getInventoryItemById($id) {
        return $this->fetchOne("SELECT * FROM inventory WHERE id = ?", [$id]);
    }

    public function addInventoryItem(array $data) {
        return $this->insert('inventory', $data);
    }

    public function updateInventoryItem($id, array $data) {
        return $this->update('inventory', $data, 'id = ?', [$id]);
    }

    public function deleteInventoryItem($id) {
        return $this->delete('inventory', 'id = ?', [$id]);
    }

    public function adjustInventoryQuantity($id, $delta) {
        // Use a single UPDATE to avoid race conditions
        $sql = "UPDATE inventory SET quantity = quantity + ? WHERE id = ?";
        $stmt = $this->execute($sql, [$delta, $id]);
        return $stmt->rowCount();
    }

    /* -----------------------------------------------------------------
     * Invoices / Billing
     * ----------------------------------------------------------------- */
    public function createInvoice(array $data) {
        // $data expected to contain patient_id, total, status (optional)
        if (!isset($data['status'])) {
            $data['status'] = 'unpaid';
        }
        return $this->insert('invoices', $data);
    }

    public function getInvoices($patientId = null) {
        if ($patientId !== null) {
            return $this->fetchAll("SELECT * FROM invoices WHERE patient_id = ? ORDER BY created_at DESC", [$patientId]);
        }
        return $this->fetchAll("SELECT * FROM invoices ORDER BY created_at DESC");
    }

    public function getInvoiceById($id) {
        return $this->fetchOne("SELECT * FROM invoices WHERE id = ?", [$id]);
    }

    public function updateInvoiceStatus($id, $status) {
        return $this->update('invoices', ['status' => $status], 'id = ?', [$id]);
    }

    /* -----------------------------------------------------------------
     * Reports / Helpers
     * ----------------------------------------------------------------- */
    public function getAdmissionsLast7Days() {
        $sql = "SELECT DATE(created_at) as day, COUNT(*) as count FROM patients WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) GROUP BY DATE(created_at) ORDER BY DATE(created_at)";
        return $this->fetchAll($sql);
    }

    public function countActivePatients() {
        $row = $this->fetchOne("SELECT COUNT(*) as c FROM patients");
        return $row ? (int)$row['c'] : 0;
    }

    public function countTodaysAppointments() {
        $row = $this->fetchOne("SELECT COUNT(*) as c FROM appointments WHERE DATE(appointment_time) = CURDATE()");
        return $row ? (int)$row['c'] : 0;
    }
}
