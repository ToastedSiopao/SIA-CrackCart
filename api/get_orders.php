<?php
session_start();
header('Content-Type: application/json');
include('../db_connect.php');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
    exit();
}

$user_id = $_SESSION['user_id'];

if ($conn) {
    $stmt = $conn->prepare("SELECT order_id, order_date, total_amount, status FROM product_orders WHERE user_id = ? ORDER BY order_date DESC");
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $orders = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $orders]);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Could not retrieve orders.']);
    }
    $stmt->close();
    $conn->close();
} else {
    http_response_code(503);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
}
?>