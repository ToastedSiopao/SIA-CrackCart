<?php
session_start();
header("Content-Type: application/json");

include("../db_connect.php");
include("../error_handler.php");
include("../log_function.php");
include("paypal_config.php");

if (!isset($_SESSION['user_id']) || !isset($_SESSION['validated_paypal_order'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request or missing validated order data.']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$paypal_order_id = $data['orderID'] ?? null;
$shipping_address_id = $data['shipping_address_id'] ?? null;

if (!$paypal_order_id || !$shipping_address_id) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing PayPal Order ID or Shipping Address.']);
    exit();
}

function get_paypal_access_token() {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, PAYPAL_API_BASE_URL . '/v1/oauth2/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
    curl_setopt($ch, CURLOPT_USERPWD, PAYPAL_CLIENT_ID . ':' . PAYPAL_CLIENT_SECRET);
    $headers = ['Accept: application/json', 'Accept-Language: en_US'];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $result = curl_exec($ch);
    if (curl_errno($ch)) { return null; }
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($http_code !== 200) { return null; }
    $json = json_decode($result);
    return $json->access_token ?? null;
}

$access_token = get_paypal_access_token();
if (!$access_token) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Could not retrieve PayPal access token.']);
    exit();
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, PAYPAL_API_BASE_URL . '/v2/checkout/orders/' . $paypal_order_id . '/capture');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $access_token
]);

$result = curl_exec($ch);
$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$capture_data = json_decode($result, true);

if ($http_status < 200 || $http_status >= 300 || $capture_data['status'] !== 'COMPLETED') {
    log_action("PayPal Capture Error", "Failed to capture payment for PayPal order {$paypal_order_id}. Response: " . json_encode($capture_data));
    echo json_encode(['status' => 'error', 'message' => 'Payment capture with PayPal failed. Please try again.']);
    exit();
}

$validated_order = $_SESSION['validated_paypal_order'];
$validated_cart = $validated_order['cart'];
$user_id = $_SESSION['user_id'];
$transaction_id = $capture_data['purchase_units'][0]['payments']['captures'][0]['id'];

// --- COUPON LOGIC ---
$applied_coupon_code = null;
$total_amount = $validated_order['total'];
if (isset($_SESSION['applied_coupon'])) {
    $coupon = $_SESSION['applied_coupon'];
    $discount_value = (float)$coupon['discount_value'];
    // The total amount was already adjusted in paypal_create_order.php, so we just need the code
    $applied_coupon_code = $coupon['coupon_code'];
}

$conn->begin_transaction();
try {
    $stmt_check_stock = $conn->prepare("SELECT STOCK FROM PRICE WHERE PRODUCER_ID = ? AND TYPE = ? FOR UPDATE");
    $stmt_update_stock = $conn->prepare("UPDATE PRICE SET STOCK = STOCK - ? WHERE PRODUCER_ID = ? AND TYPE = ?");

    foreach ($validated_cart as $item) {
        $stmt_check_stock->bind_param("is", $item['producer_id'], $item['product_type']);
        $stmt_check_stock->execute();
        $result = $stmt_check_stock->get_result();
        $product_stock = $result->fetch_assoc();

        $eggs_to_reduce = (int)$item['quantity'] * (int)$item['tray_size'];

        if (!$product_stock || $product_stock['STOCK'] < $eggs_to_reduce) {
            throw new Exception("Stock for '{$item['product_type']}' became unavailable after payment. Please contact support.");
        }

        $stmt_update_stock->bind_param("iis", $eggs_to_reduce, $item['producer_id'], $item['product_type']);
        $stmt_update_stock->execute();
    }

    $stmt_payment = $conn->prepare("INSERT INTO Payment (amount, currency, method, status, transaction_id) VALUES (?, 'PHP', 'paypal', 'completed', ?)");
    $stmt_payment->bind_param("ds", $total_amount, $transaction_id);
    $stmt_payment->execute();
    $payment_id = $stmt_payment->insert_id;

    $stmt_order = $conn->prepare(
        "INSERT INTO product_orders (user_id, total_amount, status, shipping_address_id, payment_id, paypal_order_id, vehicle_type, delivery_fee, notes, coupon_code) VALUES (?, ?, 'paid', ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt_order->bind_param("idiisssdss", $user_id, $total_amount, $shipping_address_id, $payment_id, $paypal_order_id, $validated_order['vehicle_type'], $validated_order['delivery_fee'], $validated_order['notes'], $applied_coupon_code);
    $stmt_order->execute();
    $local_order_id = $stmt_order->insert_id;

    $stmt_update_payment = $conn->prepare("UPDATE Payment SET order_id = ? WHERE payment_id = ?");
    $stmt_update_payment->bind_param("ii", $local_order_id, $payment_id);
    $stmt_update_payment->execute();

    $stmt_items = $conn->prepare("INSERT INTO product_order_items (order_id, producer_id, product_type, quantity, price_per_item, tray_size) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($validated_cart as $item) {
        $stmt_items->bind_param("iisidi", $local_order_id, $item['producer_id'], $item['product_type'], $item['quantity'], $item['price_per_tray'], $item['tray_size']);
        $stmt_items->execute();
    }
    
    // --- MARK COUPON AS USED ---
    if ($applied_coupon_code) {
        $stmt_coupon = $conn->prepare("UPDATE coupons SET is_used = 1 WHERE coupon_code = ?");
        $stmt_coupon->bind_param("s", $applied_coupon_code);
        $stmt_coupon->execute();
    }

    $conn->commit();

    unset($_SESSION['product_cart']);
    unset($_SESSION['product_cart_meta']);
    unset($_SESSION['validated_paypal_order']);
    unset($_SESSION['applied_coupon']);
    $_SESSION['latest_order_id'] = $local_order_id;

    echo json_encode(['status' => 'success', 'order_id' => $local_order_id]);

} catch (Exception $e) {
    $conn->rollback();
    log_action("CRITICAL: Order Save Failed After Payment", "User ID {$user_id}, PayPal Order {$paypal_order_id}. Error: {$e->getMessage()}");
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Your payment was successful, but we failed to save the order to our database. Please contact support immediately with your PayPal transaction ID.']);
}

$conn->close();
?>