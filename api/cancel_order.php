<?php
session_start();
header('Content-Type: application/json');
include('../db_connect.php');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['order_id'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Order ID is required.']);
    exit();
}

$order_id = $data['order_id'];

if ($conn) {
    $conn->begin_transaction();
    try {
        // First, verify the order belongs to the user and get its current status
        $stmt = $conn->prepare("SELECT status FROM product_orders WHERE order_id = ? AND user_id = ? FOR UPDATE");
        $stmt->bind_param("ii", $order_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception('Order not found or you do not have permission to cancel it.', 404);
        }

        $order = $result->fetch_assoc();
        $current_status = strtolower($order['status']);

        // Check if the order is in a cancellable state
        if ($current_status !== 'processing' && $current_status !== 'paid') {
            throw new Exception("This order cannot be cancelled as it is already {$current_status}.", 400);
        }

        // Update the order status to 'cancelled'
        $update_stmt = $conn->prepare("UPDATE product_orders SET status = 'cancelled' WHERE order_id = ?");
        $update_stmt->bind_param("i", $order_id);
        
        if (!$update_stmt->execute()) {
            throw new Exception('Failed to update the order status.', 500);
        }

        // Here you could add logic to refund the payment if it was pre-paid
        // and add the stock back into inventory. For this example, we just change the status.

        $conn->commit();
        echo json_encode(['status' => 'success', 'message' => 'Order has been successfully cancelled.']);
        $update_stmt->close();

    } catch (Exception $e) {
        $conn->rollback();
        $code = $e->getCode() > 0 ? $e->getCode() : 500;
        http_response_code($code);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    $stmt->close();
    $conn->close();
} else {
    http_response_code(503);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
}
?>