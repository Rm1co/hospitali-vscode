<?php
require 'backend/php/DatabaseConnector.php';

$db = DatabaseConnector::getInstance();

echo "All appointments:\n";
$all = $db->fetchAll('SELECT id, patient_id, staff_id, appointment_time, status FROM appointments ORDER BY appointment_time DESC LIMIT 10');
foreach ($all as $apt) {
    echo "ID: {$apt['id']}, Patient: {$apt['patient_id']}, Doctor: {$apt['staff_id']}, Time: {$apt['appointment_time']}, Status: {$apt['status']}\n";
}

echo "\n\nUpcoming scheduled appointments (future + Scheduled status):\n";
$upcoming = $db->fetchAll("SELECT id, patient_id, staff_id, appointment_time, status FROM appointments WHERE appointment_time > NOW() AND status = 'Scheduled' ORDER BY appointment_time ASC");
if (empty($upcoming)) {
    echo "No upcoming scheduled appointments found\n";
} else {
    foreach ($upcoming as $apt) {
        echo "ID: {$apt['id']}, Patient: {$apt['patient_id']}, Doctor: {$apt['staff_id']}, Time: {$apt['appointment_time']}, Status: {$apt['status']}\n";
    }
}

echo "\n\nCurrent server time: " . date('Y-m-d H:i:s') . "\n";
