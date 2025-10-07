<?php
session_start();
header("Content-Type: application/json");

include("../db_connect.php");
include("paypal_config.php");
include('../log_function.php');

function get_product_details($conn, $producer_id, $product_type) {
    if (!$conn) return null;
    $stmt = $conn->prepare("SELECT PRICE, TRAY_SIZE, STOCK FROM PRICE WHERE PRODUCER_ID = ? AND TYPE = ?");
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
    echo json_encode(['error' => 'Invalid request: Your session or cart is empty.']);
    exit();
}

$validated_cart_items = [];
$subtotal = 0;
$conn->begin_transaction();

try {
    foreach ($_SESSION['product_cart'] as $item) {
        if (!isset($item['producer_id'], $item['product_type'], $item['quantity'], $item['tray_size'])) {
            throw new Exception('A cart item is missing required data.');
        }

        $product_details = get_product_details($conn, $item['producer_id'], $item['product_type']);

        if ($product_details === null) {
            throw new Exception("Product '{$item['product_type']}' is no longer available. Please remove it from your cart.");
        }

        $available_stock_eggs = (int)$product_details['STOCK'];
        $requested_eggs = (int)$item['quantity'] * (int)$item['tray_size'];

        if ($requested_eggs > $available_stock_eggs) {
            $available_trays = floor($available_stock_eggs / (int)$item['tray_size']);
            throw new Exception(
                "Not enough stock for {$item['product_type']}. \n"
                . "Only {$available_stock_eggs} eggs left. You requested {$requested_eggs} eggs.\n"
                . "You can order a maximum of {$available_trays} trays of size {$item['tray_size']}. Please update your cart."
            );
        }

        $base_price = (float)$product_details['PRICE'];
        $base_tray_size = (int)$product_details['TRAY_SIZE'];
        $selected_tray_size = (int)$item['tray_size'];
        $adjusted_price = ($base_tray_size > 0 && $selected_tray_size !== $base_tray_size) ? ($base_price / $base_tray_size) * $selected_tray_size : $base_price;

        $item_total_price = $adjusted_price * (int)$item['quantity'];
        $subtotal += $item_total_price;
        
        $validated_cart_items[] = [
            'producer_id'  => $item['producer_id'],
            'product_type' => $item['product_type'],
            'quantity'     => (int)$item['quantity'],
            'tray_size'    => $selected_tray_size,
            'price_per_tray' => $adjusted_price
        ];
    }

    $conn->commit();

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(400);
    log_action("Checkout Stock Error", "User ID {$_SESSION['user_id']}: {$e->getMessage()}");
    echo json_encode(['error' => $e->getMessage()]);
    exit();
}

$cart_meta = $_SESSION['product_cart_meta'] ?? ['delivery_fee' => 0, 'vehicle_type' => null];
$vehicle_type = $cart_meta['vehicle_type'] ?? null;
$delivery_fee = get_delivery_fee($conn, $vehicle_type);

// --- COUPON LOGIC ---
$discount_amount = 0;
if (isset($_SESSION['applied_coupon'])) {
    $discount_amount = (float)$_SESSION['applied_coupon']['discount_value'];
}
$item_total_after_discount = $subtotal - $discount_amount;
$total_amount = round($item_total_after_discount + $delivery_fee, 2);

$_SESSION['validated_paypal_order'] = [
    'cart' => $validated_cart_items,
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
                    ],
                    'discount' => [
                        'currency_code' => 'PHP',
                        'value' => number_format($discount_amount, 2, '.', '')
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