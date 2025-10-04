<?php
session_start();
header("Content-Type: application/json");
include("../db_connect.php");

// Function to get the real price of a product from the database
function get_product_price($conn, $producer_id, $product_type) {
    // Assuming 'service_type' is the correct column name in the database for product_type
    $stmt = $conn->prepare("SELECT price FROM producer_services WHERE producer_id = ? AND service_type = ?");
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param("is", $producer_id, $product_type);
    if (!$stmt->execute()) {
        return null;
    }
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        return $result->fetch_assoc()['price'];
    }
    return null; // Product not found
}

// Function to validate and sanitize the entire cart
function validate_cart($conn) {
    if (!isset($_SESSION['product_cart']) || !is_array($_SESSION['product_cart'])) {
        return; // No cart or invalid cart structure
    }

    foreach ($_SESSION['product_cart'] as $key => &$item) {
        if (!isset($item['producer_id']) || !isset($item['product_type']) || !isset($item['price'])) {
             unset($_SESSION['product_cart'][$key]);
             continue;
        }

        $real_price = get_product_price($conn, $item['producer_id'], $item['product_type']);

        // If product no longer exists, remove it from the cart
        if ($real_price === null) {
            unset($_SESSION['product_cart'][$key]);
        } 
        // If price in session is incorrect, update it with the real price
        else if ($item['price'] != $real_price) {
            $item['price'] = $real_price;
        }
    }
    // Unset the reference to avoid side effects
    unset($item);
}

// --- Main script logic ---

if (!isset($_SESSION['product_cart'])) {
    $_SESSION['product_cart'] = [];
}

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true);

// Always validate the cart on every API call that might use it.
validate_cart($conn);

switch ($method) {
    case 'GET':
        $cart = $_SESSION['product_cart'];
        $subtotal = 0;
        $total_items = 0;
        foreach ($cart as $item) {
            $price = is_numeric($item['price']) ? $item['price'] : 0;
            $quantity = is_numeric($item['quantity']) ? $item['quantity'] : 0;
            $subtotal += $price * $quantity;
            $total_items += $quantity;
        }
        echo json_encode(['status' => 'success', 'data' => ['items' => array_values($cart), 'subtotal' => $subtotal, 'total_items' => $total_items]]);
        break;

    case 'POST':
        $action = $data['_method'] ?? 'POST';

        if ($action === 'DELETE') {
             if (isset($data['cart_item_key'])) {
                $cart_item_key = $data['cart_item_key'];
                if (isset($_SESSION['product_cart'][$cart_item_key])) {
                    unset($_SESSION['product_cart'][$cart_item_key]);
                    http_response_code(200);
                    echo json_encode(['status' => 'success', 'message' => 'Item removed from cart.']);
                } else {
                    http_response_code(404);
                    echo json_encode(['status' => 'error', 'message' => 'Item not found in cart.']);
                }
            } else {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Invalid data provided for deletion.']);
            }
        } else {
            // --- SECURELY ADD ITEM TO CART ---
            if (isset($data['producer_id'], $data['product_type'], $data['quantity'])) {
                
                $real_price = get_product_price($conn, $data['producer_id'], $data['product_type']);

                if ($real_price === null) {
                    http_response_code(404);
                    echo json_encode(['status' => 'error', 'message' => 'Product not found or is no longer available.']);
                    exit();
                }

                $cart_item_key = md5($data['producer_id'] . $data['product_type']);

                if (isset($_SESSION['product_cart'][$cart_item_key])) {
                    $_SESSION['product_cart'][$cart_item_key]['quantity'] += $data['quantity'];
                } else {
                    $_SESSION['product_cart'][$cart_item_key] = [
                        'cart_item_key' => $cart_item_key,
                        'producer_id' => $data['producer_id'],
                        'product_type' => $data['product_type'],
                        'price' => $real_price, // Use the real price from the database
                        'quantity' => $data['quantity']
                    ];
                }
                http_response_code(200);
                echo json_encode(['status' => 'success', 'message' => 'Item added to cart.']);
            } else {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Invalid data provided for adding an item.']);
            }
        }
        break;

    case 'PUT':
        if (isset($data['cart_item_key'], $data['quantity'])) {
            $cart_item_key = $data['cart_item_key'];
            $quantity = $data['quantity'];
            
            if ($quantity > 0 && is_numeric($quantity)) {
                if (isset($_SESSION['product_cart'][$cart_item_key])) {
                    $_SESSION['product_cart'][$cart_item_key]['quantity'] = $quantity;
                    http_response_code(200);
                    echo json_encode(['status' => 'success', 'message' => 'Cart updated.']);
                } else {
                    http_response_code(404);
                    echo json_encode(['status' => 'error', 'message' => 'Item not found in cart.']);
                }
            } else {
                if (isset($_SESSION['product_cart'][$cart_item_key])) {
                    unset($_SESSION['product_cart'][$cart_item_key]);
                    http_response_code(200);
                    echo json_encode(['status' => 'success', 'message' => 'Item removed from cart due to zero quantity.']);
                } else {
                     http_response_code(404);
                    echo json_encode(['status' => 'error', 'message' => 'Item not found in cart.']);
                }
            }
        } else {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid data provided for update.']);
        }
        break;

    case 'DELETE':
        if (isset($data['cart_item_key'])) {
            $cart_item_key = $data['cart_item_key'];
            if (isset($_SESSION['product_cart'][$cart_item_key])) {
                unset($_SESSION['product_cart'][$cart_item_key]);
                http_response_code(200);
                echo json_encode(['status' => 'success', 'message' => 'Item removed from cart.']);
            } else {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Item not found in cart.']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid data provided for deletion.']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
        break;
}
?>