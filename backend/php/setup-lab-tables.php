<?php
require_once 'DatabaseConnector.php';

$db = DatabaseConnector::getInstance();
$conn = $db->getConnection();

$sql = file_get_contents('../database/lab-tests-schema.sql');
$queries = array_filter(array_map('trim', explode(';', $sql)));

foreach ($queries as $query) {
    if (!empty($query)) {
        try {
            $conn->exec($query);
            echo "✓ Query executed successfully\n";
        } catch (PDOException $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }
    }
}

echo "\nDatabase setup complete!\n";
?>
