<?php
header('Content-Type: application/json');
include '../../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Basic validation
    if (empty($_POST['product_name']) || empty($_POST['producer_id']) || !isset($_POST['price'])) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields.']);
        exit;
    }

    $product_name = $_POST['product_name'];
    $producer_id = intval($_POST['producer_id']);
    $price = floatval($_POST['price']);
    $unit = $_POST['unit'] ?? 'per tray';
    $status = $_POST['status'] ?? 'active';
    $stock = intval($_POST['stock'] ?? 0);

    $query = "INSERT INTO PRICE (PRODUCER_ID, TYPE, PRICE, PER, STATUS, STOCK) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isdssi", $producer_id, $product_name, $price, $unit, $status, $stock);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Product created successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: Could not create product.']);
    }
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

$conn->close();
?>