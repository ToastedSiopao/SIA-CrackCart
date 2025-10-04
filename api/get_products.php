<?php
header('Content-Type: application/json');
include '../db_connect.php';

// Error handling for database connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

$query = "SELECT PRICE_ID, TYPE, PRODUCER_NAME, PRICE, PER FROM PRICING ORDER BY TYPE";
$result = $conn->query($query);

if ($result) {
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    echo json_encode(['status' => 'success', 'data' => $products]);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to fetch products from the database.']);
}

$conn->close();
?>
