<?php
header('Content-Type: application/json');
require_once '../db_connect.php'; 
require_once '../error_handler.php';

$response = ['status' => 'error', 'message' => 'Failed to fetch vehicles.'];

try {
    // Corrected SQL to fetch all available vehicles
    $sql = "SELECT vehicle_id, plate_no, type, capacity_trays FROM Vehicle WHERE status = 'available'";
    
    $result = $conn->query($sql);

    if ($result) {
        $vehicles = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Ensure correct data types
                $row['vehicle_id'] = intval($row['vehicle_id']);
                $row['capacity_trays'] = intval($row['capacity_trays']);
                $vehicles[] = $row;
            }
        }
        $response = ['status' => 'success', 'data' => $vehicles];
    } else {
        // This will catch errors in the SQL query itself
        throw new Exception("Query failed: " . $conn->error);
    }

} catch (Exception $e) {
    // Return a more specific error message if something goes wrong
    $response['message'] = 'Database error: ' . $e->getMessage();
}

$conn->close();
echo json_encode($response);
?>
