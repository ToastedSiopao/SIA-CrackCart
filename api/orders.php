<?php
session_start();
header("Content-Type: application/json");
include("../db_connect.php");

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch all orders for the user
$stmt = $conn->prepare("SELECT order_id, order_date, total_amount, status FROM product_orders WHERE user_id = ? ORDER BY order_date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);

if ($orders) {
    echo json_encode(['status' => 'success', 'data' => $orders]);
} else {
    echo json_encode(['status' => 'success', 'data' => []]); // Return empty array if no orders
}

$conn->close();
?>