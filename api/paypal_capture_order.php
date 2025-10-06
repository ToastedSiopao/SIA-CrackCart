<?php
session_start();
header("Content-Type: application/json");

include("../db_connect.php");
include("../error_handler.php");
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
    error_log("PayPal Capture Failed: " . json_encode($capture_data));
    echo json_encode(['status' => 'error', 'message' => 'Payment capture with PayPal failed. Please try again.']);
    exit();
}

$validated_order = $_SESSION['validated_paypal_order'];
$validated_cart = $validated_order['cart'];
$validated_total = $validated_order['total'];
$delivery_fee = $validated_order['delivery_fee'] ?? 0;
$vehicle_type = $validated_order['vehicle_type'] ?? null;
$user_id = $_SESSION['user_id'];
$transaction_id = $capture_data['purchase_units'][0]['payments']['captures'][0]['id'];

$conn->begin_transaction();
try {
    $stmt_check_stock = $conn->prepare("SELECT STOCK FROM PRICE WHERE PRODUCER_ID = ? AND TYPE = ? FOR UPDATE");
    $stmt_update_stock = $conn->prepare("UPDATE PRICE SET STOCK = STOCK - ? WHERE PRODUCER_ID = ? AND TYPE = ?");

    foreach ($validated_cart as $item) {
        $stmt_check_stock->bind_param("is", $item['producer_id'], $item['product_type']);
        $stmt_check_stock->execute();
        $result = $stmt_check_stock->get_result();
        $product_stock = $result->fetch_assoc();

        if (!$product_stock || $product_stock['STOCK'] < $item['quantity']) {
            throw new Exception("Insufficient stock for product type: " . $item['product_type']);
        }

        $stmt_update_stock->bind_param("iis", $item['quantity'], $item['producer_id'], $item['product_type']);
        $stmt_update_stock->execute();
    }

    $stmt_payment = $conn->prepare("INSERT INTO Payment (amount, currency, method, status, transaction_id) VALUES (?, 'PHP', 'paypal', 'completed', ?)");
    $stmt_payment->bind_param("ds", $validated_total, $transaction_id);
    $stmt_payment->execute();
    $payment_id = $stmt_payment->insert_id;

    $stmt_order = $conn->prepare(
        "INSERT INTO product_orders (user_id, total_amount, status, shipping_address_id, payment_id, paypal_order_id, vehicle_type, delivery_fee) VALUES (?, ?, 'paid', ?, ?, ?, ?, ?)"
    );
    $stmt_order->bind_param("idiissd", $user_id, $validated_total, $shipping_address_id, $payment_id, $paypal_order_id, $vehicle_type, $delivery_fee);
    $stmt_order->execute();
    $local_order_id = $stmt_order->insert_id;

    $stmt_update_payment = $conn->prepare("UPDATE Payment SET order_id = ? WHERE payment_id = ?");
    $stmt_update_payment->bind_param("ii", $local_order_id, $payment_id);
    $stmt_update_payment->execute();

    $stmt_items = $conn->prepare("INSERT INTO product_order_items (order_id, producer_id, product_type, quantity, price_per_item) VALUES (?, ?, ?, ?, ?)");
    foreach ($validated_cart as $item) {
        $stmt_items->bind_param("iisid", $local_order_id, $item['producer_id'], $item['product_type'], $item['quantity'], $item['price']);
        $stmt_items->execute();
    }

    $conn->commit();

    unset($_SESSION['product_cart']);
    unset($_SESSION['product_cart_meta']);
    unset($_SESSION['validated_paypal_order']);
    $_SESSION['latest_order_id'] = $local_order_id;

    echo json_encode(['status' => 'success', 'order_id' => $local_order_id]);

} catch (Exception $e) {
    $conn->rollback();
    error_log("Failed to save PayPal order: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to save order to our database after payment. Please contact support.']);
}

$conn->close();
?>
