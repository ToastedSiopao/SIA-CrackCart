<?php
session_start();
header("Content-Type: application/json");

include("../db_connect.php");
include("paypal_config.php");

if (!isset($_SESSION['user_id']) || empty($_SESSION['product_cart'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$paypal_order_id = $data['orderID'] ?? null;
$shipping_address_id = $data['shipping_address_id'] ?? null;

if (!$paypal_order_id || !$shipping_address_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing PayPal Order ID or Shipping Address']);
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
    if (curl_errno($ch)) return null;
    curl_close($ch);
    $json = json_decode($result);
    return $json->access_token ?? null;
}

$access_token = get_paypal_access_token();
if (!$access_token) {
    http_response_code(500);
    echo json_encode(['error' => 'Could not retrieve PayPal access token']);
    exit();
}

// Capture PayPal Order
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

if ($http_status !== 201 || $capture_data['status'] !== 'COMPLETED') {
    http_response_code(500);
    echo json_encode(['error' => 'Payment capture failed', 'details' => $capture_data]);
    exit();
}

// --- Payment successful, now save order to database ---

$user_id = $_SESSION['user_id'];
$product_cart = $_SESSION['product_cart'];
$subtotal = 0;
foreach ($product_cart as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$conn->begin_transaction();
try {
    $stmt = $conn->prepare("INSERT INTO product_orders (user_id, total_amount, status, shipping_address_id, paypal_order_id) VALUES (?, ?, 'paid', ?, ?)");
    $stmt->bind_param("idsi", $user_id, $subtotal, $shipping_address_id, $paypal_order_id);
    $stmt->execute();
    $local_order_id = $stmt->insert_id;

    $stmt_items = $conn->prepare("INSERT INTO product_order_items (order_id, producer_id, product_type, quantity, price_per_item) VALUES (?, ?, ?, ?, ?)");
    foreach ($product_cart as $item) {
        $stmt_items->bind_param("iisid", $local_order_id, $item['producer_id'], $item['product_type'], $item['quantity'], $item['price']);
        $stmt_items->execute();
    }

    $conn->commit();

    unset($_SESSION['product_cart']);

    echo json_encode(['status' => 'success', 'order_id' => $local_order_id]);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to save order: ' . $e->getMessage()]);
}

$conn->close();
?>