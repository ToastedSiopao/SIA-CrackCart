<?php
session_start();
header("Content-Type: application/json");

include("../db_connect.php");
include("paypal_config.php");

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
        $error_msg = curl_error($ch);
        curl_close($ch);
        return ['error' => 'Could not connect to PayPal to get token: ' . $error_msg];
    }
    
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $json = json_decode($result);

    if ($http_status >= 400 || isset($json->error)) {
        return ['error' => 'PayPal Auth Error: ' . ($json->error_description ?? 'Unknown error')];
    }
    
    return ['access_token' => $json->access_token ?? null];
}

if (!isset($_SESSION['user_id']) || !isset($_SESSION['product_cart']) || empty($_SESSION['product_cart'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request: Missing user or cart data.']);
    exit();
}

$validated_cart = [];
$subtotal = 0;

foreach ($_SESSION['product_cart'] as $item) {
    if (!isset($item['producer_id'], $item['product_type'], $item['quantity'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid cart item data.']);
        exit();
    }

    $real_price = get_product_price($conn, $item['producer_id'], $item['product_type']);

    if ($real_price === null) {
        http_response_code(404);
        echo json_encode(['error' => "Product '{$item['product_type']}' is no longer available or has an invalid price."]);
        exit();
    }

    $subtotal += $real_price * $item['quantity'];
    
    $validated_cart[] = [
        'producer_id' => $item['producer_id'],
        'product_type' => $item['product_type'],
        'quantity' => $item['quantity'],
        'price' => $real_price
    ];
}

$cart_meta = $_SESSION['product_cart_meta'] ?? ['delivery_fee' => 0, 'vehicle_type' => null];
$vehicle_type = $cart_meta['vehicle_type'] ?? null;
$delivery_fee = get_delivery_fee($conn, $vehicle_type);
$total_amount = round($subtotal + $delivery_fee, 2);

$_SESSION['validated_paypal_order'] = [
    'cart' => $validated_cart,
    'total' => $total_amount,
    'delivery_fee' => $delivery_fee,
    'notes' => $cart_meta['notes'] ?? '',
    'vehicle_type' => $vehicle_type
];

$token_response = get_paypal_access_token();
if (isset($token_response['error']) || empty($token_response['access_token'])) {
    http_response_code(500);
    echo json_encode(['error' => $token_response['error'] ?? 'Could not retrieve PayPal access token.']);
    exit();
}
$access_token = $token_response['access_token'];

$order_data = [
    'intent' => 'CAPTURE',
    'purchase_units' => [
        [
            'amount' => [
                'currency_code' => 'PHP',
                'value' => number_format($total_amount, 2, '.', ''),
                'breakdown' => [
                    'item_total' => [
                        'currency_code' => 'PHP',
                        'value' => number_format($subtotal, 2, '.', '')
                    ],
                    'shipping' => [
                        'currency_code' => 'PHP',
                        'value' => number_format($delivery_fee, 2, '.', '')
                    ]
                ]
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
curl_close($ch);

$json = json_decode($result, true);

if ($http_status >= 400) {
    $error_message = 'An error occurred with the payment process.';
    if (isset($json['details'][0]['description'])) {
       $error_message = $json['details'][0]['description'];
    }
    http_response_code($http_status);
    echo json_encode(['error' => 'PayPal API Error: ' . $error_message]);
    exit();
}

echo json_encode($json);

$conn->close();
?>
