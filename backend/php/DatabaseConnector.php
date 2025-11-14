<?php
/**
 * DatabaseConnector.php
 * PDO-based singleton database connector and simple query helpers.
 *
 * Usage:
 *   $db = DatabaseConnector::getInstance();
 *   $pdo = $db->getConnection();
 *   $rows = $db->fetchAll('SELECT * FROM patients WHERE id > ?', [0]);
 */

class DatabaseConnector
{
	/** @var DatabaseConnector|null */
	private static $instance = null;
	/** @var \PDO */
	private $pdo;

	/**
	 * Private constructor to enforce singleton.
	 */
	private function __construct()
	{
		$host = getenv('DB_HOST') ?: '127.0.0.1';
		$db   = getenv('DB_NAME') ?: 'hospital';
		$user = getenv('DB_USER') ?: 'root';
		$pass = getenv('DB_PASS') ?: 'Aa133542';
		$charset = getenv('DB_CHARSET') ?: 'utf8mb4';
		$port = getenv('DB_PORT') ?: 3306;

		$exceptions = [];

		// MariaDB/MySQL connection attempts
		try {
			$dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";
			$options = [
				PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
				PDO::ATTR_EMULATE_PREPARES   => false,
			];
			$this->pdo = new PDO($dsn, $user, $pass, $options);
			return;
		} catch (PDOException $e) {
			$exceptions[] = $e->getMessage();
		}

		// Fallback: Try to connect without database and create it
		try {
			$dsn = "mysql:host={$host};port={$port};charset={$charset}";
			$options = [
				PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
				PDO::ATTR_EMULATE_PREPARES   => false,
			];
			$this->pdo = new PDO($dsn, $user, $pass, $options);
			
			// Create database if it doesn't exist
			$this->pdo->exec("CREATE DATABASE IF NOT EXISTS `{$db}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
			
			// Select the database
			$this->pdo->exec("USE `{$db}`");
			
			return;
		} catch (PDOException $e) {
			$exceptions[] = $e->getMessage();
		}

		// All attempts failed
		throw new RuntimeException('Database connection failed: ' . end($exceptions));
	}

	/**
	 * Get singleton instance
	 * @return DatabaseConnector
	 */
	public static function getInstance()
	{
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Get raw PDO connection
	 * @return \PDO
	 */
	public function getConnection()
	{
		return $this->pdo;
	}

	/**
	 * Execute a query (SELECT expected) and return all rows
	 * @param string $sql
	 * @param array $params
	 * @return array
	 */
	public function fetchAll(string $sql, array $params = []): array
	{
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute($params);
		return $stmt->fetchAll();
	}

	/**
	 * Execute a query and return single row
	 * @param string $sql
	 * @param array $params
	 * @return array|null
	 */
	public function fetchOne(string $sql, array $params = []): ?array
	{
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute($params);
		$row = $stmt->fetch();
		return $row === false ? null : $row;
	}

	/**
	 * Execute an arbitrary statement (INSERT/UPDATE/DELETE)
	 * @param string $sql
	 * @param array $params
	 * @return int Number of affected rows
	 */
	public function query(string $sql, array $params = []): int
	{
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute($params);
		return $stmt->rowCount();
	}

	/**
	 * Insert a row into a table using associative array of column => value.
	 * Returns last insert id on success.
	 * @param string $table
	 * @param array $data
	 * @return string Last insert id
	 */
	public function insert(string $table, array $data): string
	{
		$cols = array_keys($data);
		$placeholders = array_fill(0, count($cols), '?');
		$sql = sprintf('INSERT INTO `%s` (%s) VALUES (%s)', $table, implode(',', array_map(function($c){ return "`$c`"; }, $cols)), implode(',', $placeholders));
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute(array_values($data));
		return $this->pdo->lastInsertId();
	}

	/**
	 * Update rows in table. $data is assoc array of column=>value.
	 * $where is string e.g. "id = ?" and $whereParams are the parameters for it.
	 * Returns number of affected rows.
	 * @param string $table
	 * @param array $data
	 * @param string $where
	 * @param array $whereParams
	 * @return int
	 */
	public function update(string $table, array $data, string $where, array $whereParams = []): int
	{
		$cols = array_keys($data);
		$set = implode(', ', array_map(function($c){ return "`$c` = ?"; }, $cols));
		$sql = sprintf('UPDATE `%s` SET %s WHERE %s', $table, $set, $where);
		$stmt = $this->pdo->prepare($sql);
		$params = array_values($data);
		$params = array_merge($params, $whereParams);
		$stmt->execute($params);
		return $stmt->rowCount();
	}

	/**
	 * Delete rows from table. $where is string and $whereParams are parameters.
	 * @param string $table
	 * @param string $where
	 * @param array $whereParams
	 * @return int
	 */
	public function delete(string $table, string $where, array $whereParams = []): int
	{
		$sql = sprintf('DELETE FROM `%s` WHERE %s', $table, $where);
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute($whereParams);
		return $stmt->rowCount();
	}

	// Convenience methods for patients used by endpoints
	/**
	 * Return an array of patients, optionally limited.
	 * @param int $limit
	 * @return array
	 */
	public function getPatients(int $limit = 50): array
	{
		// Use explicit integer interpolation for LIMIT to avoid driver issues with bound LIMIT
		$limit = max(1, (int)$limit);
		$sql = "SELECT * FROM `patients` ORDER BY created_at DESC LIMIT {$limit}";
		$stmt = $this->pdo->query($sql);
		return $stmt->fetchAll();
	}

	/**
	 * Get a single patient by id
	 * @param int $id
	 * @return array|null
	 */
	public function getPatientById(int $id): ?array
	{
		return $this->fetchOne('SELECT * FROM `patients` WHERE id = ?', [$id]);
	}

	/**
	 * Update a patient by id using associative data array.
	 * @param int $id
	 * @param array $data
	 * @return int affected rows
	 */
	public function updatePatient(int $id, array $data): int
	{
		return $this->update('patients', $data, 'id = ?', [$id]);
	}

	/**
	 * Delete a patient by id.
	 * @param int $id
	 * @return int affected rows
	 */
	public function deletePatient(int $id): int
	{
		return $this->delete('patients', 'id = ?', [$id]);
	}

	// Transaction helpers
	public function beginTransaction(): bool
	{
		return $this->pdo->beginTransaction();
	}
	public function commit(): bool
	{
		return $this->pdo->commit();
	}
	public function rollback(): bool
	{
		return $this->pdo->rollBack();
	}

	// Prevent cloning and unserialize
	private function __clone() {}
	public function __wakeup() { throw new \Exception('Cannot unserialize singleton'); }
}

// Backwards-compatible helper function
if (!function_exists('db')) {
	function db(): DatabaseConnector {
		return DatabaseConnector::getInstance();
	}
}

