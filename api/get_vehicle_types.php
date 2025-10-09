<?php
require_once '../db_connect.php'; 

header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'An unknown error occurred.', 'data' => []];

try {
    $query = "SELECT 
                vt.type_name AS type, 
                vt.delivery_fee,
                MIN(v.capacity_trays) as min_capacity, 
                MAX(v.capacity_trays) as max_capacity 
              FROM vehicle_types vt
              JOIN Vehicle v ON vt.type_name = v.type
              WHERE v.status = 'available'
              GROUP BY vt.type_name, vt.delivery_fee
              ORDER BY MIN(v.capacity_trays)";

    $result = $conn->query($query);

    if ($result) {
        $vehicle_types = [];
        while ($row = $result->fetch_assoc()) {
            $row['delivery_fee'] = (float)$row['delivery_fee'];
            $row['min_capacity'] = (int)$row['min_capacity'];
            $row['max_capacity'] = (int)$row['max_capacity'];
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
