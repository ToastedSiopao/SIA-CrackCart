<?php
include "../error_handler.php";
header("Content-Type: application/json");
session_start();
include("../db_connect.php");

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$order_item_id = isset($_GET['order_item_id']) ? intval($_GET['order_item_id']) : 0;

if ($order_item_id === 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Order item ID is required.']);
    exit();
}

// Prepare a statement to fetch the item details, ensuring it belongs to the logged-in user
$stmt = $conn->prepare("
    SELECT poi.product_type, poi.quantity, poi.price_per_item
    FROM product_order_items poi
    JOIN product_orders po ON poi.order_id = po.order_id
    WHERE poi.order_item_id = ? AND po.user_id = ?
");
$stmt->bind_param("ii", $order_item_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();

if ($item) {
    echo json_encode(['status' => 'success', 'data' => $item]);
} else {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Item not found or you do not have permission to view it.']);
}

$conn->close();
?>