<?php
session_start();
header("Content-Type: application/json");
include("../db_connect.php");

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);
$order_id = $data['order_id'] ?? 0;

if (empty($order_id)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Order ID is required.']);
    exit();
}

$conn->begin_transaction();

try {
    // Check the current status of the order
    $stmt_check_order = $conn->prepare("SELECT status FROM product_orders WHERE order_id = ? AND user_id = ?");
    $stmt_check_order->bind_param("ii", $order_id, $user_id);
    $stmt_check_order->execute();
    $result = $stmt_check_order->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Order not found or you do not have permission to cancel it.', 404);
    }

    $order = $result->fetch_assoc();
    $current_status = strtolower($order['status']);

    $cancellable_statuses = ['pending', 'processing', 'paid'];
    if (!in_array($current_status, $cancellable_statuses)) {
        throw new Exception("This order cannot be cancelled as its status is '{$current_status}'.", 400);
    }

    // --- RETURN STOCK (REFACTORED for EGG-BASED INVENTORY) ---
    // Fetches tray_size to correctly calculate the number of eggs to return.
    $stmt_get_items = $conn->prepare("SELECT producer_id, product_type, quantity, tray_size FROM product_order_items WHERE order_id = ?");
    $stmt_get_items->bind_param("i", $order_id);
    $stmt_get_items->execute();
    $order_items = $stmt_get_items->get_result();

    $stmt_update_stock = $conn->prepare("UPDATE PRICE SET STOCK = STOCK + ? WHERE PRODUCER_ID = ? AND TYPE = ?");

    while ($item = $order_items->fetch_assoc()) {
        // Calculate the total number of eggs to return to stock.
        $eggs_to_return = (int)$item['quantity'] * (int)$item['tray_size'];
        
        // Only update stock if a valid number of eggs is calculated.
        if ($eggs_to_return > 0) {
            $stmt_update_stock->bind_param("iis", $eggs_to_return, $item['producer_id'], $item['product_type']);
            $stmt_update_stock->execute();
        }
    }

    // Update the order status to 'cancelled'
    $stmt_update_status = $conn->prepare("UPDATE product_orders SET status = 'cancelled' WHERE order_id = ?");
    $stmt_update_status->bind_param("i", $order_id);
    
    if (!$stmt_update_status->execute()) {
        throw new Exception('Failed to update the order status.', 500);
    }

    // --- Fraud Detection Logic ---
    $seven_days_ago = date('Y-m-d H:i:s', strtotime('-7 days'));
    $stmt_cancel_count = $conn->prepare("SELECT COUNT(*) as cancellation_count FROM product_orders WHERE user_id = ? AND status = 'cancelled' AND created_at >= ?");
    $stmt_cancel_count->bind_param("is", $user_id, $seven_days_ago);
    $stmt_cancel_count->execute();
    $cancel_result = $stmt_cancel_count->get_result()->fetch_assoc();
    $cancellations = $cancel_result['cancellation_count'];

    $message = 'Order has been successfully cancelled.'; // Default message

    if ($cancellations >= 3) {
        $lock_duration = 7; // Lock for 7 days
        $lock_expires_at = date('Y-m-d H:i:s', strtotime("+{$lock_duration} days"));

        $stmt_lock_user = $conn->prepare("UPDATE USER SET ACCOUNT_STATUS = 'LOCKED', LOCK_EXPIRES_AT = ? WHERE USER_ID = ?");
        $stmt_lock_user->bind_param("si", $lock_expires_at, $user_id);
        $stmt_lock_user->execute();
        
        $message = 'Order has been successfully cancelled. Your account has been temporarily locked due to excessive cancellations.';
    }

    $conn->commit();

    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => $message]);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code($e->getCode() ?: 500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} finally {
    // Gracefully close all prepared statements
    if(isset($stmt_check_order)) $stmt_check_order->close();
    if(isset($stmt_get_items)) $stmt_get_items->close();
    if(isset($stmt_update_stock)) $stmt_update_stock->close();
    if(isset($stmt_update_status)) $stmt_update_status->close();
    if(isset($stmt_cancel_count)) $stmt_cancel_count->close();
    if(isset($stmt_lock_user)) $stmt_lock_user->close();
    $conn->close();
}
?>