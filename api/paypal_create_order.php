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
        error_log("PayPal Token cURL Error: " . curl_error($ch));
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
    // Ensure price and quantity are numeric
    if (is_numeric($item['price']) && is_numeric($item['quantity'])) {
        $subtotal += $item['price'] * $item['quantity'];
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid cart item data.']);
        exit();
    }
}
$total_amount = round($subtotal, 2);

// 3. Create PayPal Order
$access_token = get_paypal_access_token();
if (!$access_token) {
    http_response_code(500);
    echo json_encode(['error' => 'Could not retrieve PayPal access token. Payment service may be temporarily unavailable.']);
    exit();
}

$order_data = [
    'intent' => 'CAPTURE',
    'purchase_units' => [
        [
            'amount' => [
                'currency_code' => 'PHP',
                 // PayPal requires the value to be a string with two decimal places.
                'value' => number_format($total_amount, 2, '.', '')
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
$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    error_log("PayPal Create Order cURL Error: " . curl_error($ch));
    http_response_code(500);
    echo json_encode(['error' => 'Error communicating with payment gateway.']);
    exit();
}
curl_close($ch);

$json = json_decode($result, true);

// Handle potential errors from PayPal API
if ($http_status >= 400) {
    error_log("PayPal API Error: " . $result);
    $error_message = 'An error occurred with the payment process. Please try again.';
    if (isset($json['details'][0]['description'])) {
       $error_message = $json['details'][0]['description']; // Provide more specific error if available
    }
    http_response_code($http_status);
    echo json_encode(['error' => $error_message]);
    exit();
}

// Return the PayPal order ID to the client
echo json_encode($json);

?>