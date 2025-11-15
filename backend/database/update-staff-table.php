<?php
/**
 * Database Update Script
 * Adds email and password_hash columns to staff table
 */

require_once __DIR__ . '/../php/DatabaseConnector.php';

try {
    $db = DatabaseConnector::getInstance();
    $pdo = $db->getConnection();
    
    echo "Updating staff table structure...\n";
    
    // Add email column if it doesn't exist
    $pdo->exec("ALTER TABLE staff ADD COLUMN IF NOT EXISTS email VARCHAR(255) UNIQUE AFTER last_name");
    echo "✓ Added email column\n";
    
    // Add password_hash column if it doesn't exist
    $pdo->exec("ALTER TABLE staff ADD COLUMN IF NOT EXISTS password_hash VARCHAR(255) AFTER department");
    echo "✓ Added password_hash column\n";
    
    echo "\nDatabase update completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error updating database: " . $e->getMessage() . "\n";
    exit(1);
}
?>
