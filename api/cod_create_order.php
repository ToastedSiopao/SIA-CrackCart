<?php
session_start();
header('Content-Type: application/json');

include('../db_connect.php');

if (!isset($_SESSION['user_id']) || empty($_SESSION['product_cart'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user_id'];
$shipping_address_id = $data['shipping_address_id'] ?? null;

if (!$shipping_address_id) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Shipping address is required.']);
    exit();
}

$product_cart = $_SESSION['product_cart'];
$total_amount = 0;
foreach ($product_cart as $item) {
    $total_amount += $item['price'] * $item['quantity'];
}

$conn->begin_transaction();

try {
    // 1. Create the order
    $payment_method = 'Cash on Delivery';
    $order_status = 'Processing';
    $stmt = $conn->prepare("INSERT INTO product_orders (user_id, total_amount, payment_method, status, shipping_address_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("idssi", $user_id, $total_amount, $payment_method, $order_status, $shipping_address_id);
    $stmt->execute();
    $order_id = $stmt->insert_id;
    $stmt->close();

    // 2. Add items to the order_items table
    $stmt = $conn->prepare("INSERT INTO product_order_items (order_id, producer_id, product_type, quantity, price) VALUES (?, ?, ?, ?, ?)");
    foreach ($product_cart as $item) {
        $stmt->bind_param("iisid", $order_id, $item['producer_id'], $item['product_type'], $item['quantity'], $item['price']);
        $stmt->execute();
    }
    $stmt->close();

    // 3. Commit transaction
    $conn->commit();

    // 4. Clear the cart from the session
    unset($_SESSION['product_cart']);

    // 5. Return success
    echo json_encode(['status' => 'success', 'order_id' => $order_id]);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    error_log("COD Order Creation Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Failed to create order. Please try again.']);
}

$conn->close();

?>