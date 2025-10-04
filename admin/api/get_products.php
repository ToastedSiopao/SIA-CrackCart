<?php
header('Content-Type: application/json');
include '../../db_connect.php';

$query = "SELECT p.PRICE_ID, p.TYPE, pr.NAME AS PRODUCER_NAME, p.PRICE, p.PER, p.STATUS, p.STOCK 
          FROM PRICE p 
          JOIN PRODUCER pr ON p.PRODUCER_ID = pr.PRODUCER_ID 
          ORDER BY p.PRICE_ID DESC";

$result = $conn->query($query);

if ($result) {
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    echo json_encode(['status' => 'success', 'data' => $products]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Could not fetch products.']);
}

$conn->close();
?>