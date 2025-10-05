<?php
require_once '../db_connect.php'; 

header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'An unknown error occurred.', 'data' => []];

try {
    $query = "SELECT 
                type, 
                MIN(capacity_trays) as min_capacity, 
                MAX(capacity_trays) as max_capacity 
              FROM Vehicle 
              WHERE status = 'available'
              GROUP BY type
              ORDER BY MIN(capacity_trays)";

    $result = $conn->query($query);

    if ($result) {
        $vehicle_types = [];
        while ($row = $result->fetch_assoc()) {
            $vehicle_types[] = $row;
        }
        $response['status'] = 'success';
        $response['message'] = 'Vehicle types loaded successfully.';
        $response['data'] = $vehicle_types;
    } else {
        throw new Exception("Database query failed: " . $conn->error);
    }

} catch (Exception $e) {
    http_response_code(500);
    $response['message'] = $e->getMessage();
}

$conn->close();
echo json_encode($response);
?>