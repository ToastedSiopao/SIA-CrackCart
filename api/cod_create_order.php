<?php
include "../error_handler.php";
session_start();
header("Content-Type: application/json");
include("../db_connect.php");
include('../log_function.php');

function get_product_details_for_update($conn, $producer_id, $product_type) {
    if (!$conn) return null;
    $stmt = $conn->prepare("SELECT PRICE, TRAY_SIZE, STOCK FROM PRICE WHERE PRODUCER_ID = ? AND TYPE = ? FOR UPDATE");
    if (!$stmt) return null;
    $stmt->bind_param("is", $producer_id, $product_type);
    if (!$stmt->execute()) return null;
    $result = $stmt->get_result();
    return $result->num_rows > 0 ? $result->fetch_assoc() : null;
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
    $cart_meta = $_SESSION['product_cart_meta'] ?? ['vehicle_type' => null, 'delivery_fee' => 0, 'notes' => ''];
    $vehicle_type = $cart_meta['vehicle_type'] ?? null;

    $validated_items = [];
    $subtotal = 0;

    foreach ($cart as $item) {
        if (!isset($item['producer_id'], $item['product_type'], $item['quantity'], $item['tray_size'])) {
            throw new Exception('A cart item is missing required data.');
        }

        $product_details = get_product_details_for_update($conn, $item['producer_id'], $item['product_type']);

        if ($product_details === null) {
            throw new Exception("Product '{$item['product_type']}' is no longer available. Please remove it from your cart.");
        }

        $available_stock_eggs = (int)$product_details['STOCK'];
        $requested_eggs = (int)$item['quantity'] * (int)$item['tray_size'];

        if ($requested_eggs > $available_stock_eggs) {
            $available_trays = floor($available_stock_eggs / (int)$item['tray_size']);
            throw new Exception(
                "Not enough stock for {$item['product_type']}. Only {$available_stock_eggs} eggs left. You can order a maximum of {$available_trays} trays. Please update your cart."
            );
        }
        
        $base_price = (float)$product_details['PRICE'];
        $base_tray_size = (int)$product_details['TRAY_SIZE'];
        $selected_tray_size = (int)$item['tray_size'];
        $adjusted_price = ($base_tray_size > 0 && $selected_tray_size !== $base_tray_size) ? ($base_price / $base_tray_size) * $selected_tray_size : $base_price;

        $item['price'] = $adjusted_price;
        $subtotal += $item['price'] * $item['quantity'];
        $validated_items[] = $item;
    }

    $delivery_fee = get_delivery_fee($conn, $vehicle_type);
    $total_amount = $subtotal + $delivery_fee;
    
    // --- COUPON LOGIC ---
    $applied_coupon_code = null;
    if (isset($_SESSION['applied_coupon'])) {
        $coupon = $_SESSION['applied_coupon'];
        $discount_value = (float)$coupon['discount_value'];
        $total_amount -= $discount_value;
        $applied_coupon_code = $coupon['coupon_code'];
    }

    $stmt_payment = $conn->prepare("INSERT INTO Payment (amount, currency, method, status) VALUES (?, 'PHP', 'cod', 'pending')");
    $stmt_payment->bind_param("d", $total_amount);
    $stmt_payment->execute();
    $payment_id = $stmt_payment->insert_id;

    $stmt_order = $conn->prepare(
        "INSERT INTO product_orders (user_id, total_amount, status, shipping_address_id, payment_id, vehicle_type, delivery_fee, notes, coupon_code) VALUES (?, ?, 'pending', ?, ?, ?, ?, ?, ?)"
    );
    $stmt_order->bind_param("idiisdss", $user_id, $total_amount, $address_id, $payment_id, $vehicle_type, $delivery_fee, $cart_meta['notes'], $applied_coupon_code);
    $stmt_order->execute();
    $order_id = $stmt_order->insert_id;

    $stmt_update_payment = $conn->prepare("UPDATE Payment SET order_id = ? WHERE payment_id = ?");
    $stmt_update_payment->bind_param("ii", $order_id, $payment_id);
    $stmt_update_payment->execute();

    $stmt_items = $conn->prepare("INSERT INTO product_order_items (order_id, producer_id, product_type, quantity, price_per_item, tray_size) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt_stock = $conn->prepare("UPDATE PRICE SET STOCK = STOCK - ? WHERE PRODUCER_ID = ? AND TYPE = ?");

    foreach ($validated_items as $item) {
        $stmt_items->bind_param("iisidi", $order_id, $item['producer_id'], $item['product_type'], $item['quantity'], $item['price'], $item['tray_size']);
        $stmt_items->execute();
        
        $eggs_to_reduce = (int)$item['quantity'] * (int)$item['tray_size'];
        $stmt_stock->bind_param("iis", $eggs_to_reduce, $item['producer_id'], $item['product_type']);
        $stmt_stock->execute();
    }
    
    // --- MARK COUPON AS USED ---
    if ($applied_coupon_code) {
        $stmt_coupon = $conn->prepare("UPDATE coupons SET is_used = 1 WHERE coupon_code = ?");
        $stmt_coupon->bind_param("s", $applied_coupon_code);
        $stmt_coupon->execute();
    }

    $conn->commit();

    $_SESSION['product_cart'] = [];
    $_SESSION['product_cart_meta'] = ['vehicle_type' => null, 'delivery_fee' => 0, 'notes' => ''];
    $_SESSION['latest_order_id'] = $order_id;
    unset($_SESSION['applied_coupon']); // Clear the coupon after successful use

    echo json_encode(['status' => 'success', 'order_id' => $order_id]);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(400);
    log_action("COD Order Error", "User ID {$user_id}: {$e->getMessage()}");
    echo json_encode(['status' => 'error', 'message' => 'Order placement failed: ' . $e->getMessage()]);
}

$conn->close();
?>