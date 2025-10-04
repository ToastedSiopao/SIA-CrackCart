<?php
session_start();
header("Content-Type: application/json");
include("../db_connect.php");

// Initialize product cart if it doesn't exist
if (!isset($_SESSION['product_cart'])) {
    $_SESSION['product_cart'] = [];
}

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'GET':
        // Retrieve cart contents
        $cart = $_SESSION['product_cart'];
        $subtotal = 0;
        $total_items = 0;
        foreach ($cart as $item) {
            // Basic validation to prevent errors if data is malformed
            $price = is_numeric($item['price']) ? $item['price'] : 0;
            $quantity = is_numeric($item['quantity']) ? $item['quantity'] : 0;
            $subtotal += $price * $quantity;
            $total_items += $quantity;
        }
        echo json_encode(['status' => 'success', 'data' => ['items' => array_values($cart), 'subtotal' => $subtotal, 'total_items' => $total_items]]);
        break;

    case 'POST':
        // Handle different actions based on a parameter in the request body
        $action = $data['_method'] ?? 'POST'; // Use _method for method overriding

        if ($action === 'DELETE') {
            // --- REMOVE ITEM FROM CART (using POST) ---
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
            // --- ADD ITEM TO CART (default POST action) ---
            if (isset($data['producer_id'], $data['product_type'], $data['price'], $data['quantity'])) {
                $cart_item_key = md5($data['producer_id'] . $data['product_type']);

                if (isset($_SESSION['product_cart'][$cart_item_key])) {
                    $_SESSION['product_cart'][$cart_item_key]['quantity'] += $data['quantity'];
                } else {
                    $_SESSION['product_cart'][$cart_item_key] = [
                        'cart_item_key' => $cart_item_key,
                        'producer_id' => $data['producer_id'],
                        'product_type' => $data['product_type'],
                        'price' => $data['price'],
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
        // Update item quantity
        if (isset($data['cart_item_key'], $data['quantity'])) {
            $cart_item_key = $data['cart_item_key'];
            $quantity = $data['quantity'];
            
            // Basic validation
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
                // If quantity is 0 or less, remove the item
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
        // Remove item from cart (standard method)
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
