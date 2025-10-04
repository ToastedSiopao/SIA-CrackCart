<?php
session_start();
header("Content-Type: application/json");
include("../db_connect.php");

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);
$order_id = $data['order_id'] ?? 0;

if ($order_id === 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Order ID is required.']);
    exit();
}

// Update order status to 'cancelled'
$stmt = $conn->prepare("UPDATE product_orders SET status = 'cancelled' WHERE order_id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Order cancelled successfully.']);
    } else {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Order not found or already cancelled.']);
    }
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to cancel order.']);
}

$conn->close();
?>