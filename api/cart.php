<?php
session_start();
header("Content-Type: application/json");
include("../db_connect.php");

// Ensure cart and its meta data are initialized
if (!isset($_SESSION['product_cart'])) {
    $_SESSION['product_cart'] = [];
}
if (!isset($_SESSION['product_cart_meta'])) {
    $_SESSION['product_cart_meta'] = [
        'vehicle_type' => null,
        'notes' => ''
    ];
}

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
        if ($real_price === null) {
            unset($_SESSION['product_cart'][$key]);
        } else if ($item['price'] != $real_price) {
            $item['price'] = $real_price;
        }
    }
    unset($item);
}

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true);

if ($conn) {
    validate_cart($conn);
}

switch ($method) {
    case 'GET':
        $cart = $_SESSION['product_cart'] ?? [];
        $subtotal = 0;
        $total_items = 0;
        foreach ($cart as $item) {
            $subtotal += ($item['price'] ?? 0) * ($item['quantity'] ?? 0);
            $total_items += ($item['quantity'] ?? 0);
        }
        echo json_encode([
            'status' => 'success', 
            'data' => [
                'items' => array_values($cart), 
                'meta' => $_SESSION['product_cart_meta'],
                'subtotal' => $subtotal, 
                'total_items' => $total_items
            ]
        ]);
        break;

    case 'POST':
        $action = $data['action'] ?? ($data['_method'] ?? 'add');

        switch ($action) {
            case 'add':
                $item_data = $data['item'] ?? $data;

                if (isset($item_data['producer_id'], $item_data['product_type'], $item_data['quantity'])) {
                    $real_price = get_product_price($conn, $item_data['producer_id'], $item_data['product_type']);
                    if ($real_price === null) {
                        http_response_code(404);
                        echo json_encode(['status' => 'error', 'message' => 'Product could not be found.']);
                        exit();
                    }

                    $cart_item_key = md5($item_data['producer_id'] . $item_data['product_type'] . $item_data['tray_size']);
                    $quantity = filter_var($item_data['quantity'], FILTER_VALIDATE_INT);

                    if ($quantity > 0) {
                        if (isset($_SESSION['product_cart'][$cart_item_key])) {
                            $_SESSION['product_cart'][$cart_item_key]['quantity'] += $quantity;
                        } else {
                             $_SESSION['product_cart'][$cart_item_key] = [
                                'cart_item_key' => $cart_item_key,
                                'producer_id'   => $item_data['producer_id'],
                                'product_type'  => $item_data['product_type'],
                                'price'         => $real_price,
                                'quantity'      => $quantity,
                                'tray_size'     => $item_data['tray_size'] ?? 30
                            ];
                        }
                        
                        // Update cart-level meta information
                        if (isset($item_data['vehicle_type'])) {
                            $_SESSION['product_cart_meta']['vehicle_type'] = $item_data['vehicle_type'];
                        }
                        if (isset($item_data['notes'])) {
                            $_SESSION['product_cart_meta']['notes'] = $item_data['notes'];
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
                break;
            
            case 'update-meta':
                if (isset($data['vehicle_type'])) {
                    $_SESSION['product_cart_meta']['vehicle_type'] = $data['vehicle_type'];
                }
                if (isset($data['notes'])) {
                    $_SESSION['product_cart_meta']['notes'] = $data['notes'];
                }
                echo json_encode(['status' => 'success', 'message' => 'Cart details updated.']);
                break;

            case 'PUT':
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
                break;

            case 'DELETE':
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
                break;
                
            case 'clear':
                $_SESSION['product_cart'] = [];
                $_SESSION['product_cart_meta'] = ['vehicle_type' => null, 'notes' => ''];
                 echo json_encode(['status' => 'success', 'message' => 'Cart cleared.']);
                break;

            default:
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => "Invalid action: $action"]);
                break;
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
        break;
}

if ($conn) {
    $conn->close();
}
?>