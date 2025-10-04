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
$product_cart = isset($_SESSION['product_cart']) ? $_SESSION['product_cart'] : [];

if (empty($product_cart)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Cart is empty.']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$shipping_address_id = $data['shipping_address_id'] ?? 1;
$simulate_failure = $data['simulate_failure'] ?? false; // New flag

$subtotal = 0;
foreach ($product_cart as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$order_status = $simulate_failure ? 'failed' : 'paid'; // Determine order status

// Start transaction
$conn->begin_transaction();

try {
    // Insert into product_orders table
    $stmt = $conn->prepare("INSERT INTO product_orders (user_id, total_amount, status, shipping_address_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("idsi", $user_id, $subtotal, $order_status, $shipping_address_id);
    $stmt->execute();
    $order_id = $stmt->insert_id;

    // Insert into product_order_items table
    $stmt_items = $conn->prepare("INSERT INTO product_order_items (order_id, producer_id, product_type, quantity, price_per_item) VALUES (?, ?, ?, ?, ?)");
    foreach ($product_cart as $item) {
        $stmt_items->bind_param("iisid", $order_id, $item['producer_id'], $item['product_type'], $item['quantity'], $item['price']);
        $stmt_items->execute();
    }

    // Commit transaction
    $conn->commit();

    if ($order_status === 'failed') {
        http_response_code(201); // Still created, but with failed status
        echo json_encode(['status' => 'failure', 'message' => 'Order placed but payment failed.', 'order_id' => $order_id]);
    } else {
        // Clear the cart on success
        unset($_SESSION['product_cart']);
        http_response_code(201);
        echo json_encode(['status' => 'success', 'message' => 'Order placed successfully.', 'order_id' => $order_id]);
    }

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to place order: ' . $e->getMessage()]);
}

$conn->close();
?>