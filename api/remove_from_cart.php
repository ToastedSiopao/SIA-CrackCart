<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in and the cart exists
if (!isset($_SESSION['user_id']) || !isset($_SESSION['product_cart'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
    exit();
}

// Get the posted data
$data = json_decode(file_get_contents('php://input'), true);
$producer_id = $data['producer_id'] ?? null;
$product_type = $data['product_type'] ?? null;

if (!$producer_id || !$product_type) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing product identifiers.']);
    exit();
}

$cart = &$_SESSION['product_cart'];
$item_found = false;

// Find and remove the item
foreach ($cart as $key => $item) {
    if ($item['producer_id'] == $producer_id && $item['product_type'] == $product_type) {
        unset($cart[$key]);
        $item_found = true;
        break;
    }
}

if (!$item_found) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Item not found in cart.']);
    exit();
}

// Re-index the array to prevent JSON issues
$_SESSION['product_cart'] = array_values($cart);

// Recalculate subtotal
$subtotal = 0;
foreach ($_SESSION['product_cart'] as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

// Return the updated cart
echo json_encode([
    'status' => 'success',
    'message' => 'Item removed successfully.',
    'data' => [
        'items' => $_SESSION['product_cart'],
        'subtotal' => round($subtotal, 2)
    ]
]);
?>