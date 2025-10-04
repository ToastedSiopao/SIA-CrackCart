<?php
session_start();
header("Content-Type: application/json");
include("../db_connect.php");

function get_product_price($conn, $producer_id, $product_type) {
    $stmt = $conn->prepare("SELECT PRICE FROM PRICE WHERE PRODUCER_ID = ? AND TYPE = ?");
    if (!$stmt) return null;
    $stmt->bind_param("is", $producer_id, $product_type);
    if (!$stmt->execute()) return null;
    $result = $stmt->get_result();
    return $result->num_rows > 0 ? $result->fetch_assoc()['PRICE'] : null;
}

function validate_cart($conn) {
    if (!isset($_SESSION['product_cart']) || !is_array($_SESSION['product_cart'])) return;
    foreach ($_SESSION['product_cart'] as $key => &$item) {
        if (!isset($item['producer_id'], $item['product_type'], $item['price'])) {
             unset($_SESSION['product_cart'][$key]);
             continue;
        }
        $real_price = get_product_price($conn, $item['producer_id'], $item['product_type']);
        if ($real_price === null) unset($_SESSION['product_cart'][$key]);
        else if ($item['price'] != $real_price) $item['price'] = $real_price;
    }
    unset($item);
}

if (!isset($_SESSION['product_cart'])) {
    $_SESSION['product_cart'] = [];
}

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true);

if ($conn) validate_cart($conn);

switch ($method) {
    case 'GET':
        $cart = $_SESSION['product_cart'] ?? [];
        $subtotal = 0;
        $total_items = 0;
        foreach ($cart as $item) {
            $subtotal += ($item['price'] ?? 0) * ($item['quantity'] ?? 0);
            $total_items += ($item['quantity'] ?? 0);
        }
        echo json_encode(['status' => 'success', 'data' => ['items' => array_values($cart), 'subtotal' => $subtotal, 'total_items' => $total_items]]);
        break;

    case 'POST':
        $action = $data['_method'] ?? 'POST'; // Default to POST

        if ($action === 'PUT') {
            // UPDATE QUANTITY LOGIC
             if (isset($data['cart_item_key'], $data['quantity'])) {
                $cart_item_key = $data['cart_item_key'];
                $quantity = filter_var($data['quantity'], FILTER_VALIDATE_INT);

                if (isset($_SESSION['product_cart'][$cart_item_key])) {
                    if ($quantity !== false && $quantity > 0) {
                        $_SESSION['product_cart'][$cart_item_key]['quantity'] = $quantity;
                        echo json_encode(['status' => 'success', 'message' => 'Cart updated.']);
                    } else {
                        unset($_SESSION['product_cart'][$cart_item_key]);
                        echo json_encode(['status' => 'success', 'message' => 'Item removed from cart.']);
                    }
                } else {
                    http_response_code(404);
                    echo json_encode(['status' => 'error', 'message' => 'Item not found in cart.']);
                }
            } else {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Invalid data provided for update.']);
            }
        } elseif ($action === 'DELETE') {
            // DELETE ITEM LOGIC
            if (isset($data['cart_item_key'])) {
                $cart_item_key = $data['cart_item_key'];
                if (isset($_SESSION['product_cart'][$cart_item_key])) {
                    unset($_SESSION['product_cart'][$cart_item_key]);
                    echo json_encode(['status' => 'success', 'message' => 'Item removed.']);
                } else {
                    http_response_code(404);
                    echo json_encode(['status' => 'error', 'message' => 'Item not found.']);
                }
            } else {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Invalid data for deletion.']);
            }
        } else {
            // ADD ITEM LOGIC
            if (isset($data['producer_id'], $data['product_type'], $data['quantity'])) {
                $real_price = get_product_price($conn, $data['producer_id'], $data['product_type']);
                if ($real_price === null) {
                    http_response_code(404);
                    echo json_encode(['status' => 'error', 'message' => 'Product not found.']);
                    exit();
                }
                $cart_item_key = md5($data['producer_id'] . $data['product_type']);
                $quantity = filter_var($data['quantity'], FILTER_VALIDATE_INT);

                if ($quantity > 0) {
                    if (isset($_SESSION['product_cart'][$cart_item_key])) {
                        $_SESSION['product_cart'][$cart_item_key]['quantity'] += $quantity;
                    } else {
                        $_SESSION['product_cart'][$cart_item_key] = [
                            'cart_item_key' => $cart_item_key,
                            'producer_id' => $data['producer_id'],
                            'product_type' => $data['product_type'],
                            'price' => $real_price,
                            'quantity' => $quantity
                        ];
                    }
                    echo json_encode(['status' => 'success', 'message' => 'Item added to cart.']);
                } else {
                    http_response_code(400);
                    echo json_encode(['status' => 'error', 'message' => 'Invalid quantity.']);
                }
            } else {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Invalid data for adding item.']);
            }
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
        break;
}
?>