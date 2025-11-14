<?php
/**
 * Database Diagnostic Tool
 * Run this to diagnose database connection issues
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html>
<head>
    <title>Hospital System - Database Diagnostic</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #145ea8; border-bottom: 2px solid #145ea8; padding-bottom: 10px; }
        .test { margin: 20px 0; padding: 15px; border-radius: 6px; border-left: 4px solid #ccc; }
        .success { background: #ecfdf5; border-left-color: #10b981; }
        .error { background: #fef2f2; border-left-color: #dc2626; }
        .warning { background: #fffbeb; border-left-color: #f59e0b; }
        .info { background: #eff6ff; border-left-color: #3b82f6; }
        .test-title { font-weight: bold; margin-bottom: 10px; }
        .test-result { margin: 8px 0; font-family: monospace; font-size: 12px; }
        .code-block { background: #f0f0f0; padding: 12px; border-radius: 4px; margin: 10px 0; overflow-x: auto; }
        .fix { background: #e3f2fd; padding: 15px; border-radius: 4px; margin: 10px 0; }
        button { background: #145ea8; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; }
        button:hover { background: #0f4f8e; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🏥 Hospital Management System - Database Diagnostic</h1>
        <p style='background: #fffbeb; padding: 12px; border-radius: 6px; border-left: 4px solid #f59e0b;'><strong>⚠️ MariaDB Detected:</strong> This tool is optimized for both MySQL and MariaDB.</p>";

// Test 1: PHP Version
echo "<div class='test info'>
    <div class='test-title'>Test 1: PHP Version</div>
    <div class='test-result'>PHP Version: " . phpversion() . "</div>
    <div class='test-result'>PHP MySQL Extension: " . (extension_loaded('PDO') ? '✅ Loaded' : '❌ Not loaded') . "</div>
    <div class='test-result'>PHP MySQLi Extension: " . (extension_loaded('mysqli') ? '✅ Loaded' : '❌ Not loaded') . "</div>
    <div class='test-result'>PHP PDO MySQL Driver: " . (in_array('mysql', PDO::getAvailableDrivers()) ? '✅ Available' : '❌ Not available') . "</div>
</div>";

// Test 2: Environment Variables
echo "<div class='test info'>
    <div class='test-title'>Test 2: Environment Variables & Configuration</div>
    <div class='test-result'>DB_HOST: " . (getenv('DB_HOST') ?: 'Not set (using localhost)') . "</div>
    <div class='test-result'>DB_NAME: " . (getenv('DB_NAME') ?: 'Not set (using hospital)') . "</div>
    <div class='test-result'>DB_USER: " . (getenv('DB_USER') ?: 'Not set (using root)') . "</div>
    <div class='test-result'>DB_PASS: " . (getenv('DB_PASS') ? '✅ Set' : 'Not set (using Aa133542)') . "</div>
    <div class='test-result'>DB_PORT: " . (getenv('DB_PORT') ?: 'Not set (using 3306)') . "</div>
</div>";

// Test 3: MySQL Connection
$host = getenv('DB_HOST') ?: '127.0.0.1';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: 'Aa133542';
$port = getenv('DB_PORT') ?: 3306;

echo "<div class='test'>";
echo "<div class='test-title'>Test 3: MySQL Connection</div>";

try {
    $pdo = new PDO("mysql:host={$host};port={$port}", $user, $pass);
    echo "<div class='test success'>✅ Connected to MySQL Server</div>";
    echo "<div class='test-result'>Host: {$host}:{$port}</div>";
    
    // Get MySQL version
    $result = $pdo->query('SELECT VERSION() as version');
    $row = $result->fetch();
    echo "<div class='test-result'>MySQL Version: " . $row['version'] . "</div>";
    
    // Check available databases
    $result = $pdo->query('SHOW DATABASES');
    $databases = $result->fetchAll(PDO::FETCH_COLUMN);
    echo "<div class='test-result'>Available Databases: " . implode(', ', $databases) . "</div>";
    
    // Check if hospital database exists
    if (in_array('hospital', $databases)) {
        echo "<div class='test success'>✅ Hospital database exists</div>";
    } else {
        echo "<div class='test warning'>⚠️ Hospital database does not exist. Creating it...</div>";
        try {
            $pdo->exec('CREATE DATABASE IF NOT EXISTS `hospital` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
            echo "<div class='test success'>✅ Hospital database created successfully</div>";
        } catch (Exception $e) {
            echo "<div class='test error'>❌ Failed to create hospital database: " . $e->getMessage() . "</div>";
        }
    }
    
    // Check user authentication method
    try {
        $result = $pdo->query("SELECT user, host, plugin FROM mysql.user WHERE user='{$user}'");
        $users = $result->fetchAll();
        echo "<div class='test-result'>User Authentication Methods:</div>";
        foreach ($users as $u) {
            echo "<div class='test-result'>&nbsp;&nbsp;• {$u['user']}@{$u['host']} → {$u['plugin']}</div>";
        }
    } catch (Exception $e) {
        echo "<div class='test warning'>⚠️ Could not check user authentication method</div>";
    }
    
} catch (PDOException $e) {
    echo "<div class='test error'>❌ Failed to connect to MySQL</div>";
    echo "<div class='test-result'>Error: " . $e->getMessage() . "</div>";
    
    echo "<div class='fix'>
        <strong>Possible Solutions:</strong>
        <ul>
            <li>Ensure MySQL is running (Windows: Run 'Services' and start MySQL)</li>
            <li>Verify MySQL user credentials</li>
            <li>Check if MySQL is listening on port 3306</li>
            <li>Try connecting with: mysql -u {$user} -p -h {$host} -P {$port}</li>
        </ul>
    </div>";
}

// Test 4: Hospital Database Tables
echo "<div class='test'>";
echo "<div class='test-title'>Test 4: Hospital Database Tables</div>";

try {
    $pdo = new PDO("mysql:host={$host};port={$port};dbname=hospital", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $result = $pdo->query('SHOW TABLES');
    $tables = $result->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "<div class='test warning'>⚠️ No tables found in hospital database</div>";
        echo "<div class='fix'>
            <strong>Next Step:</strong> Run the database schema to create tables.
            <div class='code-block'>Look for: backend/database/schema-complete.sql</div>
        </div>";
    } else {
        echo "<div class='test success'>✅ Database contains " . count($tables) . " tables</div>";
        echo "<div class='test-result'>Tables: " . implode(', ', $tables) . "</div>";
    }
    
} catch (PDOException $e) {
    echo "<div class='test error'>❌ Could not access hospital database</div>";
    echo "<div class='test-result'>Error: " . $e->getMessage() . "</div>";
}

// Test 5: Credentials Test
echo "<div class='test'>";
echo "<div class='test-title'>Test 5: Quick Credential Check</div>";

$credentials_to_try = [
    ['host' => '127.0.0.1', 'user' => 'root', 'pass' => 'Aa133542'],
    ['host' => 'localhost', 'user' => 'root', 'pass' => 'Aa133542'],
    ['host' => '127.0.0.1', 'user' => 'root', 'pass' => ''],
    ['host' => 'localhost', 'user' => 'root', 'pass' => ''],
];

foreach ($credentials_to_try as $cred) {
    try {
        $pdo = new PDO("mysql:host={$cred['host']};port=3306", $cred['user'], $cred['pass']);
        echo "<div class='test success'>✅ Works: {$cred['host']} | User: {$cred['user']} | Pass: " . ($cred['pass'] ? 'Aa133542' : 'empty') . "</div>";
    } catch (Exception $e) {
        // Silently skip failed attempts
    }
}

echo "</div>";

// Summary
echo "<div class='test info'>
    <div class='test-title'>📋 Summary & Next Steps</div>
    <ol>
        <li>If connection fails, ensure MySQL is running</li>
        <li>If hospital database doesn't exist, it will be created automatically</li>
        <li>If authentication fails, try different credentials above</li>
        <li>Update backend/php/DatabaseConnector.php with correct credentials if needed</li>
        <li>Run the schema file to create database tables</li>
        <li>Return to http://localhost/hospitali-vscode-1/ to test</li>
    </ol>
</div>";

echo "
    </div>
</body>
</html>";
?>
