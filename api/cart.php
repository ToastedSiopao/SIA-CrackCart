<?php
session_start();
header("Content-Type: application/json");
include("../db_connect.php");

// --- Initialization ---
if (!isset($_SESSION['product_cart'])) {
    $_SESSION['product_cart'] = [];
}
if (!isset($_SESSION['product_cart_meta'])) {
    $_SESSION['product_cart_meta'] = [
        'vehicle_type' => null,
        'delivery_fee' => 0.0,
        'notes' => ''
    ];
}

// --- Helper Functions ---
function get_product_details($conn, $producer_id, $product_type) {
    $stmt = $conn->prepare("SELECT PRICE, TRAY_SIZE, STOCK FROM PRICE WHERE PRODUCER_ID = ? AND TYPE = ?");
    $stmt->bind_param("is", $producer_id, $product_type);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0 ? $result->fetch_assoc() : null;
}

// --- Request Handling ---
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $cart_items = [];
        $subtotal = 0.0;

        foreach ($_SESSION['product_cart'] as $cart_item_key => $item) {
            $item['price'] = (float)$item['price_per_tray'];
            $item['total_price'] = $item['price'] * (int)$item['quantity'];
            $cart_items[] = $item;
            $subtotal += $item['total_price'];
        }

        // Ensure all values are floats for calculation
        $delivery_fee = (float)($_SESSION['product_cart_meta']['delivery_fee'] ?? 0.0);
        $discount_amount = isset($_SESSION['applied_coupon']) ? (float)$_SESSION['applied_coupon']['discount_value'] : 0.0;
        $grand_total = $subtotal + $delivery_fee - $discount_amount;

        $response_data = [
            'items' => array_values($cart_items),
            'subtotal' => $subtotal,
            // expose delivery_fee at top-level for convenience AND keep meta
            'delivery_fee' => $delivery_fee,
            'grand_total' => $grand_total,
            'meta' => [
                // keep meta structure but force numeric delivery_fee inside meta as well
                'vehicle_type' => $_SESSION['product_cart_meta']['vehicle_type'] ?? null,
                'delivery_fee' => $delivery_fee,
                'notes' => $_SESSION['product_cart_meta']['notes'] ?? ''
            ],
            'applied_coupon' => $_SESSION['applied_coupon'] ?? null
        ];
        
        echo json_encode(['status' => 'success', 'data' => $response_data]);
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $action = $data['action'] ?? 'add';

        switch ($action) {
            case 'add':
                $item_data = $data['item'] ?? null;
                if (!$item_data || !isset($item_data['producer_id'], $item_data['product_type'], $item_data['quantity'])) {
                    http_response_code(400);
                    echo json_encode(['status' => 'error', 'message' => 'Invalid data for adding item.']);
                    break;
                }

                // Safely coerce incoming delivery_fee to float (prevents empty string → stored "")
                $raw_fee = $item_data['delivery_fee'] ?? 0.0;
                $delivery_fee = is_numeric($raw_fee) ? (float)$raw_fee : 0.0;

                // Always update cart meta fields when provided (keeps session consistent)
                $_SESSION['product_cart_meta']['vehicle_type'] = $item_data['vehicle_type'] ?? $_SESSION['product_cart_meta']['vehicle_type'];
                $_SESSION['product_cart_meta']['delivery_fee'] = $delivery_fee;
                $_SESSION['product_cart_meta']['notes'] = $item_data['notes'] ?? $_SESSION['product_cart_meta']['notes'];

                $product_details = get_product_details($conn, $item_data['producer_id'], $item_data['product_type']);
                if (!$product_details) {
                    http_response_code(404);
                    echo json_encode(['status' => 'error', 'message' => 'Product not found.']);
                    break;
                }

                $available_stock = (int)$product_details['STOCK'];
                $selected_tray_size = (int)($item_data['tray_size'] ?? 30);
                $quantity_to_add = filter_var($item_data['quantity'], FILTER_VALIDATE_INT);
                
                if ($quantity_to_add <= 0) {
                    http_response_code(400);
                    echo json_encode(['status' => 'error', 'message' => 'Invalid quantity.']);
                    break;
                }

                $cart_item_key = md5($item_data['producer_id'] . $item_data['product_type'] . $selected_tray_size);
                $eggs_to_add = $quantity_to_add * $selected_tray_size;
                $eggs_in_cart = isset($_SESSION['product_cart'][$cart_item_key]) ? (int)$_SESSION['product_cart'][$cart_item_key]['quantity'] * (int)$_SESSION['product_cart'][$cart_item_key]['tray_size'] : 0;
                
                if (($eggs_in_cart + $eggs_to_add) > $available_stock) {
                    http_response_code(400);
                    echo json_encode(['status' => 'error', 'message' => "Stock unavailable. Only {$available_stock} eggs remaining."]);
                    break;
                }
                
                $price_per_tray = (float)($item_data['price'] ?? 0.0);

                if (isset($_SESSION['product_cart'][$cart_item_key])) {
                    $_SESSION['product_cart'][$cart_item_key]['quantity'] += $quantity_to_add;
                } else {
                    $_SESSION['product_cart'][$cart_item_key] = [
                        'cart_item_key'  => $cart_item_key,
                        'producer_id'    => $item_data['producer_id'],
                        'product_type'   => $item_data['product_type'],
                        'price_per_tray' => $price_per_tray,
                        'quantity'       => $quantity_to_add,
                        'tray_size'      => $selected_tray_size
                    ];
                }
                echo json_encode(['status' => 'success', 'message' => 'Item added to cart.']);
                break;

            case 'update':
            case 'delete':
                $cart_item_key = $data['cart_item_key'] ?? null;
                if (!$cart_item_key || !isset($_SESSION['product_cart'][$cart_item_key])) {
                    http_response_code(400); 
                    echo json_encode(['status' => 'error', 'message' => 'Invalid item.']); 
                    break;
                }
                if ($action === 'delete') {
                    unset($_SESSION['product_cart'][$cart_item_key]);
                } else {
                    $quantity = filter_var($data['quantity'], FILTER_VALIDATE_INT);
                    if ($quantity > 0) {
                        $_SESSION['product_cart'][$cart_item_key]['quantity'] = $quantity;
                    } else {
                        unset($_SESSION['product_cart'][$cart_item_key]);
                    }
                }
                // If cart is empty after update/delete, reset meta
                if (empty($_SESSION['product_cart'])) {
                    $_SESSION['product_cart_meta'] = ['vehicle_type' => null, 'delivery_fee' => 0.0, 'notes' => ''];
                }
                echo json_encode(['status' => 'success', 'message' => 'Cart updated.']);
                break;

            default:
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
                break;
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => "Method {$method} not allowed."]);
        break;
}

if ($conn) {
    $conn->close();
}
?>
