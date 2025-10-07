<?php
session_start();
header("Content-Type: application/json");
include("../db_connect.php");

// 1. AUTHENTICATION & VALIDATION
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$product_cart = $_SESSION['product_cart'] ?? [];
$cart_meta = $_SESSION['product_cart_meta'] ?? [];

if (empty($product_cart)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Cart is empty.']);
    exit();
}

// 2. GETTING DATA FROM THE REQUEST
$data = json_decode(file_get_contents('php://input'), true);
$shipping_address_id = $data['shipping_address_id'] ?? null;
$payment_method = $data['payment_method'] ?? 'card';
$vehicle_type = $cart_meta['vehicle_type'] ?? 'Not specified';

if (empty($shipping_address_id)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Shipping address is required.']);
    exit();
}

// 3. DETERMINE ORDER STATUS
$order_status = '';
if ($payment_method === 'cod') {
    $order_status = 'processing'; 
} elseif ($payment_method === 'card' || $payment_method === 'paypal') {
    $order_status = $data['order_status'] ?? 'pending'; 
} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid payment method.']);
    exit();
}

// 4. CALCULATE TOTAL
$subtotal = 0;
foreach ($product_cart as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

// 5. DATABASE TRANSACTION
$conn->begin_transaction();

try {
    // --- STOCK CHECK REFACTORED for EGG-BASED INVENTORY ---
    // Now checks against total egg count.
    $stmt_check_stock = $conn->prepare("SELECT STOCK FROM PRICE WHERE PRODUCER_ID = ? AND TYPE = ? FOR UPDATE");
    
    foreach ($product_cart as $key => $item) {
        if (!isset($item['producer_id'], $item['product_type'], $item['quantity'], $item['tray_size'])) {
            throw new Exception("Invalid cart item data for product: " . ($item['product_type'] ?? 'Unknown'));
        }

        $stmt_check_stock->bind_param("is", $item['producer_id'], $item['product_type']);
        $stmt_check_stock->execute();
        $product = $stmt_check_stock->get_result()->fetch_assoc();
        
        if (!$product) {
            throw new Exception("Product not found: " . $item['product_type']);
        }
        
        // Calculate the total number of eggs required for the order.
        $eggs_required = (int)$item['quantity'] * (int)$item['tray_size'];

        // Check if there are enough individual eggs in stock.
        if ((int)$product['STOCK'] < $eggs_required) {
            throw new Exception("Insufficient stock for product: " . $item['product_type'] . ". Required eggs: " . $eggs_required . ", Available: " . $product['STOCK']);
        }
    }


    // INSERT ORDER
    $stmt_order = $conn->prepare("INSERT INTO product_orders (user_id, total_amount, status, shipping_address_id, payment_method, vehicle_type) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt_order->bind_param("idsiss", $user_id, $subtotal, $order_status, $shipping_address_id, $payment_method, $vehicle_type);
    $stmt_order->execute();
    $order_id = $stmt_order->insert_id;

    // --- INSERT ORDER ITEMS & UPDATE STOCK REFACTORED ---
    // Now deducts the exact number of eggs.
    $stmt_items = $conn->prepare("INSERT INTO product_order_items (order_id, producer_id, product_type, quantity, price_per_item, tray_size) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt_update_stock = $conn->prepare("UPDATE PRICE SET STOCK = STOCK - ? WHERE PRODUCER_ID = ? AND TYPE = ?");
    
    $items_for_confirmation = [];

    foreach ($product_cart as $key => $item) {
        $eggs_to_deduct = (int)$item['quantity'] * (int)$item['tray_size'];
        $tray_size = (int)$item['tray_size'];

        // Insert order item, now including tray_size
        $stmt_items->bind_param("iissdi", $order_id, $item['producer_id'], $item['product_type'], $item['quantity'], $item['price'], $tray_size);
        $stmt_items->execute();

        // Update stock by deducting the exact number of eggs.
        $stmt_update_stock->bind_param("iis", $eggs_to_deduct, $item['producer_id'], $item['product_type']);
        $stmt_update_stock->execute();
        
        // Prepare item for confirmation page
        $items_for_confirmation[] = [
            'product_type' => $item['product_type'],
            'quantity' => $item['quantity'],
            'price_per_item' => $item['price'],
            'tray_size' => $tray_size
        ];
    }

    $conn->commit();

    // 6. PREPARE SESSION FOR CONFIRMATION PAGE
    $_SESSION['last_order_id'] = $order_id;
    $_SESSION['last_order_details'] = [
        'order_id' => $order_id,
        'total_amount' => $subtotal,
        'items' => $items_for_confirmation
    ];

    unset($_SESSION['product_cart']);
    unset($_SESSION['product_cart_meta']);

    // 7. SEND SUCCESS RESPONSE
    http_response_code(201);
    echo json_encode(['status' => 'success', 'message' => 'Order placed successfully.', 'order_id' => $order_id]);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to place order: ' . $e->getMessage()]);
} finally {
    // Gracefully close all prepared statements
    if (isset($stmt_check_stock)) $stmt_check_stock->close();
    if (isset($stmt_order)) $stmt_order->close();
    if (isset($stmt_items)) $stmt_items->close();
    if (isset($stmt_update_stock)) $stmt_update_stock->close();
    $conn->close();
}
?>