<?php
header('Content-Type: application/json');
include '../../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id'] ?? 0);

    if ($product_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid product ID.']);
        exit;
    }
    
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

    $query = "UPDATE PRICE SET PRODUCER_ID = ?, TYPE = ?, PRICE = ?, PER = ?, STATUS = ?, STOCK = ? WHERE PRICE_ID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isdssii", $producer_id, $product_name, $price, $unit, $status, $stock, $product_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Product updated successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: Could not update product.']);
    }
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

$conn->close();
?>