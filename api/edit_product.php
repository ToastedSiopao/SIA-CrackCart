<?php
session_start();
header('Content-Type: application/json');

include '../db_connect.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit();
}

$price_id = filter_input(INPUT_POST, 'price_id', FILTER_VALIDATE_INT);
$type = trim($_POST['type'] ?? '');
$producer_name = trim($_POST['producer_name'] ?? '');
$price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
$per = trim($_POST['per'] ?? '');
$status = trim($_POST['status'] ?? 'active');
$stock = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);

if (!$price_id || empty($type) || empty($producer_name) || $price === false || empty($per) || $stock === false) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid input. Please check all fields.']);
    exit();
}

try {
    $stmt = $conn->prepare("UPDATE PRICE SET TYPE = ?, PRODUCER_NAME = ?, PRICE = ?, PER = ?, STATUS = ?, STOCK = ? WHERE PRICE_ID = ?");
    $stmt->bind_param("ssdssii", $type, $producer_name, $price, $per, $status, $stock, $price_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Product updated successfully!']);
        } else {
            echo json_encode(['status' => 'success', 'message' => 'No changes were made to the product.']);
        }
    } else {
        throw new Exception('Database update failed.');
    }
    $stmt->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'A server error occurred: ' . $e->getMessage()]);
}

$conn->close();
?>
