<?php
require 'backend/php/DatabaseConnector.php';

$db = DatabaseConnector::getInstance();
$pdo = $db->getConnection();

// Add appointments for tomorrow
$tomorrow = date('Y-m-d', strtotime('+1 day'));
$futureTime1 = $tomorrow . ' 10:00:00';
$futureTime2 = $tomorrow . ' 14:30:00';
$futureTime3 = date('Y-m-d', strtotime('+2 days')) . ' 09:00:00';

$stmt = $pdo->prepare('INSERT INTO appointments (patient_id, staff_id, appointment_time, status, notes) VALUES (?, ?, ?, ?, ?)');

$stmt->execute([2, 13, $futureTime1, 'Scheduled', 'Follow-up checkup']);
echo "Added appointment for $futureTime1\n";

$stmt->execute([3, 13, $futureTime2, 'Scheduled', 'Regular consultation']);
echo "Added appointment for $futureTime2\n";

$stmt->execute([4, 13, $futureTime3, 'Scheduled', 'Lab results review']);
echo "Added appointment for $futureTime3\n";

echo "\nUpcoming scheduled appointments for doctor 13:\n";
$upcoming = $db->fetchAll("SELECT id, patient_id, appointment_time, notes FROM appointments WHERE staff_id = 13 AND appointment_time > NOW() AND status = 'Scheduled' ORDER BY appointment_time ASC");
foreach ($upcoming as $apt) {
    echo "ID: {$apt['id']}, Patient: {$apt['patient_id']}, Time: {$apt['appointment_time']}, Notes: {$apt['notes']}\n";
}
