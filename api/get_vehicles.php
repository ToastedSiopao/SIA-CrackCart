<?php
header('Content-Type: application/json');
require_once '../db_connect.php';

// Select all necessary vehicle details
$query = "SELECT vehicle_id, type, plate_no, capacity_trays FROM Vehicle WHERE status = 'available' ORDER BY capacity_trays ASC";

$result = $conn->query($query);

$vehicles = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $vehicles[] = $row;
    }
}

echo json_encode(['status' => 'success', 'data' => $vehicles]);

$conn->close();
?>
