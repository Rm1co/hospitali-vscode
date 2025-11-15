<?php

class DatabaseConnector
{
	private static $instance = null;
	private $pdo;

	private function __construct()
	{
<<<<<<< Updated upstream
		$host = getenv('DB_HOST') ?: '127.0.0.1';
		$db   = getenv('DB_NAME') ?: 'hospital';
=======
		$host = getenv('DB_HOST') ?: 'localhost';
		$db   = getenv('DB_NAME') ?: 'hospitali_db';
>>>>>>> Stashed changes
		$user = getenv('DB_USER') ?: 'root';
		$pass = getenv('DB_PASS') ?: 'Ben254wa@#net01H';
		$charset = getenv('DB_CHARSET') ?: 'utf8mb4';
		$port = getenv('DB_PORT') ?: 3306;

		$exceptions = [];

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

		try {
			$dsn = "mysql:host={$host};port={$port};charset={$charset}";
			$options = [
				PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
				PDO::ATTR_EMULATE_PREPARES   => false,
			];
			$this->pdo = new PDO($dsn, $user, $pass, $options);
			
			$this->pdo->exec("CREATE DATABASE IF NOT EXISTS `{$db}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
			
			$this->pdo->exec("USE `{$db}`");
			
			return;
		} catch (PDOException $e) {
			$exceptions[] = $e->getMessage();
		}

		throw new RuntimeException('Database connection failed: ' . end($exceptions));
	}

	public static function getInstance()
	{
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function getConnection()
	{
		return $this->pdo;
	}

	public function fetchAll(string $sql, array $params = []): array
	{
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute($params);
		return $stmt->fetchAll();
	}

	public function fetchOne(string $sql, array $params = []): ?array
	{
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute($params);
		$row = $stmt->fetch();
		return $row === false ? null : $row;
	}

	public function query(string $sql, array $params = []): int
	{
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute($params);
		return $stmt->rowCount();
	}

	public function insert(string $table, array $data): string
	{
		$cols = array_keys($data);
		$placeholders = array_fill(0, count($cols), '?');
		$sql = sprintf('INSERT INTO `%s` (%s) VALUES (%s)', $table, implode(',', array_map(function($c){ return "`$c`"; }, $cols)), implode(',', $placeholders));
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute(array_values($data));
		return $this->pdo->lastInsertId();
	}

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

	public function delete(string $table, string $where, array $whereParams = []): int
	{
		$sql = sprintf('DELETE FROM `%s` WHERE %s', $table, $where);
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute($whereParams);
		return $stmt->rowCount();
	}

	public function getPatients(int $limit = 50): array
	{
		$limit = max(1, (int)$limit);
		$sql = "SELECT * FROM `patients` ORDER BY created_at DESC LIMIT {$limit}";
		$stmt = $this->pdo->query($sql);
		return $stmt->fetchAll();
	}

	public function getPatientById(int $id): ?array
	{
		return $this->fetchOne('SELECT * FROM `patients` WHERE id = ?', [$id]);
	}

	public function updatePatient(int $id, array $data): int
	{
		return $this->update('patients', $data, 'id = ?', [$id]);
	}

	public function deletePatient(int $id): int
	{
		return $this->delete('patients', 'id = ?', [$id]);
	}

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

