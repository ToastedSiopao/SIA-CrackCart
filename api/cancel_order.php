<?php
session_start();
header("Content-Type: application/json");
include("../db_connect.php");
include("../log_function.php"); 

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
    $stmt_check_order = $conn->prepare("SELECT status FROM product_orders WHERE order_id = ? AND user_id = ?");
    $stmt_check_order->bind_param("ii", $order_id, $user_id);
    $stmt_check_order->execute();
    $result = $stmt_check_order->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Order not found or you do not have permission to modify it.', 404);
    }

    $order = $result->fetch_assoc();
    $current_status = strtolower($order['status']);

    $cancellable_statuses = ['pending', 'processing', 'paid'];
    if (!in_array($current_status, $cancellable_statuses)) {
        throw new Exception("This order cannot be cancelled as its status is '{$current_status}'.", 400);
    }

    $stmt_update_status = $conn->prepare("UPDATE product_orders SET status = 'cancellation requested' WHERE order_id = ?");
    $stmt_update_status->bind_param("i", $order_id);
    
    if (!$stmt_update_status->execute()) {
        throw new Exception('Failed to request order cancellation.', 500);
    }

    $conn->commit();
    
    log_activity($conn, $user_id, "Cancellation Requested", "User requested to cancel order #{$order_id}");

    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => 'Your request to cancel the order has been submitted.']);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code($e->getCode() ?: 500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} finally {
    if(isset($stmt_check_order)) $stmt_check_order->close();
    if(isset($stmt_update_status)) $stmt_update_status->close();
    $conn->close();
}
?>