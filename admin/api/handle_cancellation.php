<?php
session_start();
header("Content-Type: application/json");
include("../../db_connect.php");
include("../../log_function.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Access denied.']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$order_id = $data['order_id'] ?? 0;
$action = $data['action'] ?? ''; // 'approved' or 'denied'

if (empty($order_id) || !in_array($action, ['approved', 'denied'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid input.']);
    exit();
}

$conn->begin_transaction();

try {
    $stmt_get_order = $conn->prepare("SELECT user_id, status FROM product_orders WHERE order_id = ?");
    $stmt_get_order->bind_param("i", $order_id);
    $stmt_get_order->execute();
    $result = $stmt_get_order->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Order not found.', 404);
    }

    $order = $result->fetch_assoc();
    $user_id = $order['user_id'];
    $current_status = $order['status'];

    if ($current_status !== 'cancellation requested') {
        throw new Exception("This order is not pending cancellation approval.", 400);
    }

    $new_status = '';
    $log_message = '';

    if ($action === 'approved') {
        $new_status = 'cancelled';
        log_activity($conn, $_SESSION['user_id'], "Cancellation Approved", "Admin approved cancellation for order #{$order_id}");

        // Return stock
        $stmt_get_items = $conn->prepare("SELECT producer_id, product_type, quantity, tray_size FROM product_order_items WHERE order_id = ?");
        $stmt_get_items->bind_param("i", $order_id);
        $stmt_get_items->execute();
        $order_items = $stmt_get_items->get_result();

        $stmt_update_stock = $conn->prepare("UPDATE PRICE SET STOCK = STOCK + ? WHERE PRODUCER_ID = ? AND TYPE = ?");

        while ($item = $order_items->fetch_assoc()) {
            $eggs_to_return = (int)$item['quantity'] * (int)$item['tray_size'];
            if ($eggs_to_return > 0) {
                $stmt_update_stock->bind_param("iis", $eggs_to_return, $item['producer_id'], $item['product_type']);
                $stmt_update_stock->execute();
            }
        }
    } else { // Denied
        // Revert to previous status (simple logic: assume 'processing')
        $new_status = 'processing'; 
        log_activity($conn, $_SESSION['user_id'], "Cancellation Denied", "Admin denied cancellation for order #{$order_id}");
    }

    $stmt_update_status = $conn->prepare("UPDATE product_orders SET status = ? WHERE order_id = ?");
    $stmt_update_status->bind_param("si", $new_status, $order_id);
    $stmt_update_status->execute();

    $conn->commit();

    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => "Cancellation request has been {$action}."]);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code($e->getCode() ?: 500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>