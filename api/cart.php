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
    $stmt = $conn->prepare("SELECT PRICE, TRAY_SIZE, STOCK FROM PRICE WHERE PRODUCER_ID = ? AND TYPE = ? ORDER BY TRAY_SIZE DESC LIMIT 1");
    $stmt->bind_param("is", $producer_id, $product_type);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0 ? $result->fetch_assoc() : null;
}

function get_cart_data($conn) {
    $cart_items = [];
    $subtotal = 0.0;
    $total_trays = 0;

    if (empty($_SESSION['product_cart'])) {
        $_SESSION['product_cart_meta'] = ['vehicle_type' => null, 'delivery_fee' => 0.0, 'notes' => ''];
        unset($_SESSION['applied_coupon']);
    }

    foreach ($_SESSION['product_cart'] as $cart_item_key => $item) {
        $item['cart_item_key'] = $cart_item_key;

        if ((!isset($item['price']) || (float)$item['price'] <= 0) && isset($item['price_per_tray'])) {
            $item['price'] = (float)$item['price_per_tray'];
        }

        $item_price = (float)($item['price'] ?? 0.0);
        $item_quantity = (int)($item['quantity'] ?? 0);
        
        $item['total_price'] = $item_price * $item_quantity;
        $cart_items[] = $item;
        $subtotal += $item['total_price'];
        $total_trays += $item_quantity;
    }
    
    // --- Delivery Fee Calculation ---
    $vehicle_type = 'Motorcycle'; // Default
    if ($total_trays > 55) {
        $vehicle_type = 'Truck';
    } else if ($total_trays > 15) {
        $vehicle_type = 'Car';
    }

    $delivery_fee = 0.0;
    if ($total_trays > 0) {
        $stmt = $conn->prepare("SELECT delivery_fee FROM vehicle_types WHERE type_name = ?");
        $stmt->bind_param("s", $vehicle_type);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $delivery_fee = (float)$row['delivery_fee'];
        }
        $stmt->close();
    }
    
    $_SESSION['product_cart_meta']['vehicle_type'] = $vehicle_type;
    $_SESSION['product_cart_meta']['delivery_fee'] = $delivery_fee;

    $discount_amount = isset($_SESSION['applied_coupon']) ? (float)$_SESSION['applied_coupon']['discount_value'] : 0.0;
    $grand_total = $subtotal + $delivery_fee - $discount_amount;

    return [
        'items' => array_values($cart_items),
        'subtotal' => $subtotal,
        'delivery_fee' => $delivery_fee,
        'grand_total' => $grand_total,
        'meta' => $_SESSION['product_cart_meta'],
        'applied_coupon' => $_SESSION['applied_coupon'] ?? null
    ];
}

// --- Request Handling ---
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        echo json_encode(['status' => 'success', 'data' => get_cart_data($conn)]);
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

                $product_details = get_product_details($conn, $item_data['producer_id'], $item_data['product_type']);
                if (!$product_details || (int)$product_details['TRAY_SIZE'] <= 0) {
                    http_response_code(404);
                    echo json_encode(['status' => 'error', 'message' => 'Product not found or has invalid data.']);
                    break;
                }

                $selected_tray_size = (int)($item_data['tray_size'] ?? 30);
                $quantity_to_add = filter_var($item_data['quantity'], FILTER_VALIDATE_INT);
                $cart_item_key = md5($item_data['producer_id'] . $item_data['product_type'] . $selected_tray_size);

                $price_per_egg = (float)$product_details['PRICE'] / (int)$product_details['TRAY_SIZE'];
                $current_price_for_selected_size = $price_per_egg * $selected_tray_size;

                if (isset($_SESSION['product_cart'][$cart_item_key])) {
                    $_SESSION['product_cart'][$cart_item_key]['quantity'] += $quantity_to_add;
                } else {
                    $_SESSION['product_cart'][$cart_item_key] = [
                        'cart_item_key'      => $cart_item_key,
                        'producer_id'        => $item_data['producer_id'],
                        'product_type'       => $item_data['product_type'],
                        'price'              => number_format($current_price_for_selected_size, 2, '.', ''),
                        'quantity'           => $quantity_to_add,
                        'tray_size'          => $selected_tray_size,
                        'original_price'     => (float)$product_details['PRICE'],
                        'original_tray_size' => (int)$product_details['TRAY_SIZE']
                    ];
                }
                echo json_encode(['status' => 'success', 'message' => 'Item added to cart.', 'data' => get_cart_data($conn)]);
                break;

            case 'update':
                $cart_item_key = $data['cart_item_key'] ?? null;
                $quantity = filter_var($data['quantity'], FILTER_VALIDATE_INT);
                if ($cart_item_key && isset($_SESSION['product_cart'][$cart_item_key]) && $quantity > 0) {
                    $_SESSION['product_cart'][$cart_item_key]['quantity'] = $quantity;
                } else if ($cart_item_key && isset($_SESSION['product_cart'][$cart_item_key])) {
                    unset($_SESSION['product_cart'][$cart_item_key]);
                }
                echo json_encode(['status' => 'success', 'message' => 'Cart updated.', 'data' => get_cart_data($conn)]);
                break;

            case 'delete':
                $cart_item_key = $data['cart_item_key'] ?? null;
                if ($cart_item_key && isset($_SESSION['product_cart'][$cart_item_key])) {
                    unset($_SESSION['product_cart'][$cart_item_key]);
                }
                echo json_encode(['status' => 'success', 'message' => 'Item removed.', 'data' => get_cart_data($conn)]);
                break;

            case 'update_tray_size':
                $cart_item_key = $data['cart_item_key'] ?? null;
                $new_tray_size = filter_var($data['tray_size'] ?? 0, FILTER_VALIDATE_INT);

                if (!$cart_item_key || !$new_tray_size || !isset($_SESSION['product_cart'][$cart_item_key])) {
                    http_response_code(400);
                    echo json_encode(['status' => 'error', 'message' => 'Invalid item or tray size provided.']);
                    break;
                }

                $item = $_SESSION['product_cart'][$cart_item_key];
                if ((int)$item['tray_size'] === $new_tray_size) {
                    echo json_encode(['status' => 'success', 'message' => 'Tray size is already up to date.', 'data' => get_cart_data($conn)]);
                    break;
                }
                
                if (!isset($item['original_price']) || !isset($item['original_tray_size']) || $item['original_tray_size'] == 0) {
                    $product_details = get_product_details($conn, $item['producer_id'], $item['product_type']);
                    if ($product_details) {
                        $item['original_price'] = (float)$product_details['PRICE'];
                        $item['original_tray_size'] = (int)$product_details['TRAY_SIZE'];
                    } else {
                        http_response_code(404); 
                        echo json_encode(['status' => 'error', 'message' => 'Could not find product to update price.']);
                        break;
                    }
                }

                $price_per_egg = (float)$item['original_price'] / (int)$item['original_tray_size'];
                $new_price = $price_per_egg * $new_tray_size;
                $new_key = md5($item['producer_id'] . $item['product_type'] . $new_tray_size);

                if (isset($_SESSION['product_cart'][$new_key])) {
                    $_SESSION['product_cart'][$new_key]['quantity'] += $item['quantity'];
                    unset($_SESSION['product_cart'][$cart_item_key]);
                } else {
                    $new_cart_order = [];
                    foreach ($_SESSION['product_cart'] as $key => $value) {
                        if ($key === $cart_item_key) {
                            $item['tray_size'] = $new_tray_size;
                            $item['price'] = number_format($new_price, 2, '.', '');
                            $item['cart_item_key'] = $new_key;
                            $new_cart_order[$new_key] = $item;
                        } else {
                            $new_cart_order[$key] = $value;
                        }
                    }
                    $_SESSION['product_cart'] = $new_cart_order;
                }

                echo json_encode(['status' => 'success', 'message' => 'Tray size updated successfully.', 'data' => get_cart_data($conn)]);
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