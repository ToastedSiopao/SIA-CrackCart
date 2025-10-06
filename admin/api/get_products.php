<?php
header('Content-Type: application/json');
include '../../db_connect.php';

// Check for database connection errors
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

// Corrected query to include the TRAY_SIZE column for the admin panel
$query = "SELECT 
            p.PRICE_ID, 
            p.TYPE, 
            pr.NAME AS PRODUCER_NAME, 
            p.PRICE, 
            p.PER, 
            p.STATUS, 
            p.STOCK,
            p.TRAY_SIZE 
          FROM 
            PRICE p 
          JOIN 
            PRODUCER pr ON p.PRODUCER_ID = pr.PRODUCER_ID 
          ORDER BY 
            p.PRICE_ID DESC";

$result = $conn->query($query);

if ($result) {
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    echo json_encode(['status' => 'success', 'data' => $products]);
} else {
    http_response_code(500);
    // Provide a more detailed error message for debugging
    echo json_encode(['status' => 'error', 'message' => 'Failed to fetch products: ' . $conn->error]);
}

$conn->close();
?>
