<?php
header('Content-Type: application/json');
include('../../db_connect.php');

session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Access denied.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit();
}

// Basic validation
$product_id = intval($_POST['product_id'] ?? 0);
$product_name = trim($_POST['product_name'] ?? '');
$producer_id = intval($_POST['producer_id'] ?? 0);
$price = floatval($_POST['price'] ?? 0);
$unit = trim($_POST['unit'] ?? 'per tray');

if ($product_id <= 0 || empty($product_name) || $producer_id <= 0 || $price <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid data provided.']);
    exit();
}

if ($conn) {
    try {
        $stmt = $conn->prepare("UPDATE PRICE SET PRODUCER_ID = ?, TYPE = ?, PRICE = ?, PER = ? WHERE PRICE_ID = ?");
        $stmt->bind_param("isdsi", $producer_id, $product_name, $price, $unit, $product_id);

        if ($stmt->execute()) {
             if ($stmt->affected_rows > 0) {
                echo json_encode(['status' => 'success', 'message' => 'Product updated successfully.']);
            } else {
                echo json_encode(['status' => 'success', 'message' => 'No changes were made to the product.']);
            }
        } else {
            throw new Exception('Failed to update the product.');
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    $stmt->close();
    $conn->close();
} else {
    http_response_code(503);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
}
?>