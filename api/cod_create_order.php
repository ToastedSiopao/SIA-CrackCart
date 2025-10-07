<?php
include "../error_handler.php";
session_start();
header("Content-Type: application/json");
include("../db_connect.php");

function get_product_price($conn, $producer_id, $product_type) {
    $stmt = $conn->prepare("SELECT PRICE FROM PRICE WHERE PRODUCER_ID = ? AND TYPE = ?");
    $stmt->bind_param("is", $producer_id, $product_type);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0 ? $result->fetch_assoc()['PRICE'] : null;
}

function get_delivery_fee($conn, $vehicle_type) {
    if (!$vehicle_type) return 0;
    $stmt = $conn->prepare("SELECT delivery_fee FROM vehicle_types WHERE type_name = ?");
    $stmt->bind_param("s", $vehicle_type);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0 ? (float)$result->fetch_assoc()['delivery_fee'] : 0;
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

if (!isset($_SESSION['product_cart']) || empty($_SESSION['product_cart'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Your cart is empty.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);
$address_id = $data['shipping_address_id'] ?? null;

if (empty($address_id)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Shipping address is missing.']);
    exit();
}

$conn->begin_transaction();

try {
    $cart = $_SESSION['product_cart'];
    $cart_meta = $_SESSION['product_cart_meta'] ?? ['delivery_fee' => 0, 'vehicle_type' => null];
    $vehicle_type = $cart_meta['vehicle_type'] ?? null;
    
    $subtotal = 0;
    foreach ($cart as $key => &$item) {
        $real_price = get_product_price($conn, $item['producer_id'], $item['product_type']);
        if ($real_price === null) {
            throw new Exception("Product '{$item['product_type']}' is no longer available.");
        }
        $item['price'] = $real_price;
        $subtotal += $item['price'] * $item['quantity'];
    }
    unset($item); 

    $delivery_fee = get_delivery_fee($conn, $vehicle_type);
    $total_amount = $subtotal + $delivery_fee;

    $stmt_payment = $conn->prepare("INSERT INTO Payment (amount, currency, method, status) VALUES (?, 'PHP', 'cod', 'pending')");
    $stmt_payment->bind_param("d", $total_amount);
    $stmt_payment->execute();
    $payment_id = $stmt_payment->insert_id;

    $stmt_order = $conn->prepare(
        "INSERT INTO product_orders (user_id, total_amount, status, shipping_address_id, payment_id, vehicle_type, delivery_fee) VALUES (?, ?, 'pending', ?, ?, ?, ?)"
    );
    $stmt_order->bind_param("idiisd", $user_id, $total_amount, $address_id, $payment_id, $vehicle_type, $delivery_fee);
    $stmt_order->execute();
    $order_id = $stmt_order->insert_id;

    $stmt_update_payment = $conn->prepare("UPDATE Payment SET order_id = ? WHERE payment_id = ?");
    $stmt_update_payment->bind_param("ii", $order_id, $payment_id);
    $stmt_update_payment->execute();

    $stmt_items = $conn->prepare("INSERT INTO product_order_items (order_id, producer_id, product_type, quantity, price_per_item) VALUES (?, ?, ?, ?, ?)");
    $stmt_stock = $conn->prepare("UPDATE PRICE SET STOCK = STOCK - ? WHERE PRODUCER_ID = ? AND TYPE = ?");

    foreach ($cart as $item) {
        $stmt_items->bind_param("iisid", $order_id, $item['producer_id'], $item['product_type'], $item['quantity'], $item['price']);
        $stmt_items->execute();

        $stmt_stock->bind_param("iis", $item['quantity'], $item['producer_id'], $item['product_type']);
        $stmt_stock->execute();
    }

    $conn->commit();

    $_SESSION['product_cart'] = [];
    $_SESSION['product_cart_meta'] = ['vehicle_type' => null, 'delivery_fee' => 0];
    $_SESSION['latest_order_id'] = $order_id;

    echo json_encode(['status' => 'success', 'order_id' => $order_id]);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Order placement failed: ' . $e->getMessage()]);
}

$conn->close();
?>
