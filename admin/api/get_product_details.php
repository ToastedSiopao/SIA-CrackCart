<?php
header('Content-Type: application/json');
include('../../db_connect.php');

session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Access denied.']);
    exit();
}

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid product ID.']);
    exit();
}

if ($conn) {
    try {
        $stmt = $conn->prepare("SELECT PRODUCER_ID, TYPE, PRICE, PER FROM PRICE WHERE PRICE_ID = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $product = $result->fetch_assoc();
            echo json_encode(['status' => 'success', 'data' => $product]);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Product not found.']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to fetch product details.']);
    }
    $stmt->close();
    $conn->close();
} else {
    http_response_code(503);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
}
?>