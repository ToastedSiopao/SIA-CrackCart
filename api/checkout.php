<?php
session_start();
header("Content-Type: application/json");
include("../db_connect.php");

// 1. AUTHENTICATION & VALIDATION
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

// 2. GETTING DATA FROM THE REQUEST
$data = json_decode(file_get_contents('php://input'), true);
$shipping_address_id = $data['shipping_address_id'] ?? 1; // Default to 1 if not provided
$payment_method = $data['payment_method'] ?? 'card'; // Default to 'card'

// 3. DETERMINE ORDER STATUS BASED ON PAYMENT METHOD
$order_status = '';
if ($payment_method === 'cod') {
    $order_status = 'processing'; // For Cash on Delivery, admin needs to fulfill it
} elseif ($payment_method === 'card') {
    $order_status = 'paid'; // For card payments, assume it's paid
} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid payment method.']);
    exit();
}

// 4. CALCULATE TOTAL
$subtotal = 0;
foreach ($product_cart as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

// 5. DATABASE TRANSACTION
$conn->begin_transaction();

try {
    // Insert into product_orders table
    $stmt = $conn->prepare("INSERT INTO product_orders (user_id, total_amount, status, shipping_address_id, payment_method) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("idsis", $user_id, $subtotal, $order_status, $shipping_address_id, $payment_method);
    $stmt->execute();
    $order_id = $stmt->insert_id;

    // Insert into product_order_items table
    $stmt_items = $conn->prepare("INSERT INTO product_order_items (order_id, producer_id, product_type, quantity, price_per_item) VALUES (?, ?, ?, ?, ?)");
    foreach ($product_cart as $item) {
        $stmt_items->bind_param("iisid", $order_id, $item['producer_id'], $item['product_type'], $item['quantity'], $item['price']);
        $stmt_items->execute();
    }

    $conn->commit();

    // 6. PREPARE SESSION FOR CONFIRMATION PAGE
    $_SESSION['last_order_id'] = $order_id;
    $_SESSION['last_order_details'] = [
        'order_id' => $order_id,
        'total_amount' => $subtotal,
        'items' => $product_cart // Pass the cart items
    ];

    // Clear the cart from the session
    unset($_SESSION['product_cart']);

    // 7. SEND SUCCESS RESPONSE
    http_response_code(201);
    echo json_encode(['status' => 'success', 'message' => 'Order placed successfully.', 'order_id' => $order_id]);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to place order: ' . $e->getMessage()]);
}

$conn->close();
?>