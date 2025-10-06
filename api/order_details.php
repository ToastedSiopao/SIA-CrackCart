<?php
include "../error_handler.php";
session_start();
header("Content-Type: application/json");
include("../db_connect.php");

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id === 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Order ID is required.']);
    exit();
}

// Fetch the main order details
$stmt = $conn->prepare("SELECT po.*, a.address_line1 AS street, a.city, a.state, a.zip_code, a.country FROM product_orders po JOIN user_addresses a ON po.shipping_address_id = a.address_id WHERE po.order_id = ? AND po.user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Order not found.']);
    exit();
}

// Fetch order items and join with returns to get return status for each specific item.
// This now works because the `returns` table has the `order_item_id` column.
$stmt_items = $conn->prepare("
    SELECT 
        poi.order_item_id, 
        poi.product_type, 
        poi.price_per_item, 
        poi.quantity, 
        r.status AS return_status 
    FROM 
        product_order_items poi
    LEFT JOIN 
        returns r ON poi.order_item_id = r.order_item_id
    WHERE 
        poi.order_id = ?
");
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$items_result = $stmt_items->get_result();
$items = $items_result->fetch_all(MYSQLI_ASSOC);

$order['items'] = $items;

echo json_encode(['status' => 'success', 'data' => $order]);

$conn->close();
?>