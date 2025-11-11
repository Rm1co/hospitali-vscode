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
    private $host = 'localhost';
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
}
