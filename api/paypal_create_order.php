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

// 1. Get Access Token
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
    if (curl_errno($ch)) {
        return null;
    }
    curl_close($ch);
    $json = json_decode($result);
    return $json->access_token ?? null;
}

// 2. Calculate Total
$product_cart = $_SESSION['product_cart'];
$subtotal = 0;
foreach ($product_cart as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$total_amount = round($subtotal, 2);

// 3. Create PayPal Order
$access_token = get_paypal_access_token();
if (!$access_token) {
    http_response_code(500);
    echo json_encode(['error' => 'Could not retrieve PayPal access token']);
    exit();
}

$order_data = [
    'intent' => 'CAPTURE',
    'purchase_units' => [
        [
            'amount' => [
                'currency_code' => 'PHP',
                'value' => (string)$total_amount
            ]
        ]
    ]
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, PAYPAL_API_BASE_URL . '/v2/checkout/orders');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($order_data));
$headers = [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $access_token
];
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$result = curl_exec($ch);
if(curl_errno($ch)) {
    http_response_code(500);
    echo json_encode(['error' => 'Error creating PayPal order: ' . curl_error($ch)]);
    exit();
}
curl_close($ch);

$json = json_decode($result);

// Return the PayPal order ID to the client
echo json_encode($json);

?>