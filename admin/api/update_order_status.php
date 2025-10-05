<?php
require_once '../../db_connect.php';
require_once '../../session_handler.php';

// Admin-only access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Access denied.']);
    exit;
}

header('Content-Type: application/json');
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['order_id']) || !isset($input['status'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Order ID and status are required.']);
    exit;
}

$order_id = $input['order_id'];
$new_status = $input['status'];
$valid_statuses = ['To Pay', 'To Ship', 'To Receive', 'Completed', 'Cancelled'];

if (!in_array($new_status, $valid_statuses)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid status provided.']);
    exit;
}

$conn->begin_transaction();

try {
    // Check current order status
    $stmt_check = $conn->prepare("SELECT status FROM product_orders WHERE order_id = ?");
    $stmt_check->bind_param("i", $order_id);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    if ($result->num_rows === 0) {
        throw new Exception('Order not found.', 404);
    }
    $current_order = $result->fetch_assoc();
    $current_status = $current_order['status'];
    $stmt_check->close();

    // If changing status to 'Cancelled' and it wasn't already cancelled, return stock
    if ($new_status === 'Cancelled' && $current_status !== 'Cancelled') {
        $stmt_get_items = $conn->prepare("SELECT producer_id, product_type, quantity FROM product_order_items WHERE order_id = ?");
        $stmt_get_items->bind_param("i", $order_id);
        $stmt_get_items->execute();
        $order_items = $stmt_get_items->get_result();
        
        $stmt_update_stock = $conn->prepare("UPDATE PRICE SET STOCK = STOCK + ? WHERE PRODUCER_ID = ? AND TYPE = ?");
        while ($item = $order_items->fetch_assoc()) {
            $stmt_update_stock->bind_param("iis", $item['quantity'], $item['producer_id'], $item['product_type']);
            if(!$stmt_update_stock->execute()) {
                throw new Exception("Failed to return stock for product: " . $item['product_type'], 500);
            }
        }
        $stmt_get_items->close();
        $stmt_update_stock->close();
    }

    // Update the order status
    $stmt_update = $conn->prepare("UPDATE product_orders SET status = ? WHERE order_id = ?");
    $stmt_update->bind_param("si", $new_status, $order_id);
    
    if (!$stmt_update->execute()) {
        throw new Exception('Failed to update order status.', 500);
    }
    $stmt_update->close();
    
    $conn->commit();
    echo json_encode(['status' => 'success', 'message' => "Order #$order_id status updated to $new_status."]);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code($e->getCode() > 0 ? $e->getCode() : 500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
?>