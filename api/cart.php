<?php
session_start();
header("Content-Type: application/json");
include("../db_connect.php");

// Initialize product cart if it doesn't exist
if (!isset($_SESSION['product_cart'])) {
    $_SESSION['product_cart'] = [];
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Retrieve cart contents
        $cart = $_SESSION['product_cart'];
        $subtotal = 0;
        $total_items = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
            $total_items += $item['quantity'];
        }
        echo json_encode(['status' => 'success', 'data' => ['items' => array_values($cart), 'subtotal' => $subtotal, 'total_items' => $total_items]]);
        break;

    case 'POST':
        // Add item to cart
        $data = json_decode(file_get_contents('php://input'), true);

        if (isset($data['producer_id'], $data['product_type'], $data['price'], $data['quantity'])) {
            $cart_item_key = md5($data['producer_id'] . $data['product_type']);

            if (isset($_SESSION['product_cart'][$cart_item_key])) {
                $_SESSION['product_cart'][$cart_item_key]['quantity'] += $data['quantity'];
            } else {
                $_SESSION['product_cart'][$cart_item_key] = [
                    'cart_item_key' => $cart_item_key, // Add key for easier reference on the client
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
            echo json_encode(['status' => 'error', 'message' => 'Invalid data provided.']);
        }
        break;

    case 'PUT':
        // Update item quantity
        $data = json_decode(file_get_contents('php://input'), true);

        if (isset($data['cart_item_key'], $data['quantity'])) {
            $cart_item_key = $data['cart_item_key'];
            if (isset($_SESSION['product_cart'][$cart_item_key])) {
                $_SESSION['product_cart'][$cart_item_key]['quantity'] = $data['quantity'];
                http_response_code(200);
                echo json_encode(['status' => 'success', 'message' => 'Cart updated.']);
            } else {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Item not found in cart.']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid data provided.']);
        }
        break;

    case 'DELETE':
        // Remove item from cart
        $data = json_decode(file_get_contents('php://input'), true);

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
            echo json_encode(['status' => 'error', 'message' => 'Invalid data provided.']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
        break;
}
?>