<?php
include "../error_handler.php";
session_start();
header("Content-Type: application/json");
include("../db_connect.php");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit();
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

$address_id = $data['address_id'] ?? null;
$cart = $data['cart'] ?? [];

if (empty($address_id) || empty($cart)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing address or cart items']);
    exit();
}

// Calculate total amount
$total_amount = 0;
foreach ($cart as $item) {
    $total_amount += $item['price'] * $item['quantity'];
}

// Use a transaction to ensure atomicity
$conn->begin_transaction();

try {
    // Create an entry in the Payment table
    $stmt_payment = $conn->prepare("INSERT INTO Payment (amount, currency, method, status) VALUES (?, 'PHP', 'cod', 'pending')");
    $stmt_payment->bind_param("d", $total_amount);
    $stmt_payment->execute();
    $payment_id = $stmt_payment->insert_id;

    // Create the order in the product_orders table
    $stmt_order = $conn->prepare("INSERT INTO product_orders (user_id, total_amount, status, shipping_address_id, payment_id) VALUES (?, ?, 'pending', ?, ?)");
    $stmt_order->bind_param("idis", $user_id, $total_amount, $address_id, $payment_id);
    $stmt_order->execute();
    $order_id = $stmt_order->insert_id;

    // Update the Payment table with the order_id
    $stmt_update_payment = $conn->prepare("UPDATE Payment SET order_id = ? WHERE payment_id = ?");
    $stmt_update_payment->bind_param("ii", $order_id, $payment_id);
    $stmt_update_payment->execute();

    // Add items to the product_order_items table
    $stmt_items = $conn->prepare("INSERT INTO product_order_items (order_id, producer_id, product_type, quantity, price_per_item) VALUES (?, ?, ?, ?, ?)");
    foreach ($cart as $item) {
        $stmt_items->bind_param("iisid", $order_id, $item['producer_id'], $item['type'], $item['quantity'], $item['price']);
        $stmt_items->execute();
    }

    $conn->commit();

    echo json_encode(['status' => 'success', 'order_id' => $order_id]);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Order placement failed: ' . $e->getMessage()]);
}

$conn->close();
?>