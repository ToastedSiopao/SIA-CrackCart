<?php
session_start();
header("Content-Type: application/json");
include("../db_connect.php");

// --- Initialization and Helper Functions ---
if (!isset($_SESSION['product_cart'])) {
    $_SESSION['product_cart'] = [];
}
if (!isset($_SESSION['product_cart_meta'])) {
    $_SESSION['product_cart_meta'] = [
        'vehicle_type' => null,
        'delivery_fee' => 0,
        'notes' => ''
    ];
}

// Updated function to get price, tray size, and stock
function get_product_details($conn, $producer_id, $product_type) {
    if (!$conn) return null;
    // Now fetches STOCK as well.
    $stmt = $conn->prepare("SELECT PRICE, TRAY_SIZE, STOCK FROM PRICE WHERE PRODUCER_ID = ? AND TYPE = ?");
    if (!$stmt) return null;
    $stmt->bind_param("is", $producer_id, $product_type);
    if (!$stmt->execute()) return null;
    $result = $stmt->get_result();
    return $result->num_rows > 0 ? $result->fetch_assoc() : null;
}

// Cart validation function to ensure data integrity
function validate_cart($conn) {
    // ... (existing validation logic remains the same)
}

// --- Request Handling ---

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true);

validate_cart($conn);

switch ($method) {
    case 'GET':
        // ... (existing GET logic remains the same)
        break;

    case 'POST':
        $action = $data['action'] ?? 'add'; // Default action

        switch ($action) {
            case 'add':
                $item_data = $data['item'] ?? $data;
                if (!isset($item_data['producer_id'], $item_data['product_type'], $item_data['quantity'])) {
                    http_response_code(400);
                    echo json_encode(['status' => 'error', 'message' => 'Invalid data for adding item.']);
                    break;
                }
                
                $product_details = get_product_details($conn, $item_data['producer_id'], $item_data['product_type']);
                
                if ($product_details === null) {
                    http_response_code(404);
                    echo json_encode(['status' => 'error', 'message' => 'Product could not be found.']);
                    break;
                }

                // --- REFACTORED STOCK CHECK ---
                $available_stock = (int)$product_details['STOCK'];
                $selected_tray_size = isset($item_data['tray_size']) ? (int)$item_data['tray_size'] : 30;
                $cart_item_key = md5($item_data['producer_id'] . $item_data['product_type'] . $selected_tray_size);

                $quantity_to_add = filter_var($item_data['quantity'], FILTER_VALIDATE_INT);
                if ($quantity_to_add <= 0) {
                    http_response_code(400);
                    echo json_encode(['status' => 'error', 'message' => 'Invalid quantity.']);
                    break;
                }
                
                $eggs_to_add = $quantity_to_add * $selected_tray_size;

                $eggs_in_cart = 0;
                if (isset($_SESSION['product_cart'][$cart_item_key])) {
                    $existing_item = $_SESSION['product_cart'][$cart_item_key];
                    $eggs_in_cart = (int)$existing_item['quantity'] * (int)$existing_item['tray_size'];
                }

                $total_eggs_required = $eggs_in_cart + $eggs_to_add;

                if ($total_eggs_required > $available_stock) {
                    http_response_code(400);
                    $eggs_remaining = $available_stock - $eggs_in_cart;
                    $trays_remaining = ($eggs_remaining > 0 && $selected_tray_size > 0) ? floor($eggs_remaining / $selected_tray_size) : 0;
                    
                    $message = "Cannot add item. Only {$available_stock} eggs are available for {$item_data['product_type']}.";
                    if ($eggs_in_cart > 0) {
                        $message .= " You already have {$eggs_in_cart} eggs in your cart.";
                    }
                    if ($trays_remaining > 0) {
                        $message .= " You can add up to {$trays_remaining} more tray(s) of {$selected_tray_size}.";
                    } else {
                        $message .= " You cannot add any more of this item.";
                    }

                    echo json_encode(['status' => 'error', 'message' => $message]);
                    break; 
                }
                // --- END STOCK CHECK ---

                $base_price = (float)$product_details['PRICE'];
                $base_tray_size = (int)$product_details['TRAY_SIZE'];
                $adjusted_price = ($base_tray_size > 0 && $selected_tray_size !== $base_tray_size) ? ($base_price / $base_tray_size) * $selected_tray_size : $base_price;

                if (isset($_SESSION['product_cart'][$cart_item_key])) {
                    $_SESSION['product_cart'][$cart_item_key]['quantity'] += $quantity_to_add;
                } else {
                     $_SESSION['product_cart'][$cart_item_key] = [
                        'cart_item_key' => $cart_item_key,
                        'producer_id'   => $item_data['producer_id'],
                        'product_type'  => $item_data['product_type'],
                        'price'         => $adjusted_price,
                        'quantity'      => $quantity_to_add,
                        'tray_size'     => $selected_tray_size
                    ];
                }
                
                // Update meta info
                if (isset($item_data['vehicle_type'])) $_SESSION['product_cart_meta']['vehicle_type'] = $item_data['vehicle_type'];
                if (isset($item_data['delivery_fee'])) $_SESSION['product_cart_meta']['delivery_fee'] = (float)$item_data['delivery_fee'];
                if (isset($item_data['notes'])) $_SESSION['product_cart_meta']['notes'] = $item_data['notes'];
                
                echo json_encode(['status' => 'success', 'message' => 'Item added to cart.']);
                break;

            // ... (other cases: update, delete, clear remain the same)
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => "Method {$method} Not Allowed"]);
        break;
}

if ($conn) {
    $conn->close();
}
?>