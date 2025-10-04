<?php
include "../error_handler.php";
session_start();
header("Content-Type: application/json");
include("../db_connect.php");

// (Function from cart.php - duplicated for safety in case of direct call)
function get_product_price($conn, $producer_id, $product_type) {
    $stmt = $conn->prepare("SELECT PRICE FROM PRICE WHERE PRODUCER_ID = ? AND TYPE = ?");
    $stmt->bind_param("is", $producer_id, $product_type);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        return $result->fetch_assoc()['PRICE'];
    }
    return null;
}

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

// IGNORE the cart sent from the client. Use the one from the session which is validated.
if (!isset($_SESSION['product_cart']) || empty($_SESSION['product_cart'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Your cart is empty.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);
$address_id = $data['address_id'] ?? null;

if (empty($address_id)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Shipping address is missing.']);
    exit();
}

// Use a transaction to ensure atomicity
$conn->begin_transaction();

try {
    $cart = $_SESSION['product_cart'];
    $total_amount = 0;
    
    // --- SERVER-SIDE VALIDATION & CALCULATION ---
    // Recalculate the total amount on the server side based on prices from the database.
    foreach ($cart as $key => &$item) {
        $real_price = get_product_price($conn, $item['producer_id'], $item['product_type']);
        if ($real_price === null) {
            // If a product suddenly becomes unavailable, throw an error.
            throw new Exception("Product '{$item['product_type']}' is no longer available.");
        }
        $item['price'] = $real_price; // Ensure the price is correct.
        $total_amount += $item['price'] * $item['quantity'];
    }
    unset($item); // Unset reference

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
        // CRITICAL FIX: Use 'product_type' from the validated session cart
        $stmt_items->bind_param("iisid", $order_id, $item['producer_id'], $item['product_type'], $item['quantity'], $item['price']);
        $stmt_items->execute();
    }

    $conn->commit();

    // Clear the cart after successful order placement
    $_SESSION['product_cart'] = [];

    echo json_encode(['status' => 'success', 'order_id' => $order_id]);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    // Provide a more user-friendly error message
    echo json_encode(['status' => 'error', 'message' => 'Order placement failed. ' . $e->getMessage()]);
}

$conn->close();
?>