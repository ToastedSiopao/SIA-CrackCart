<?php
session_start();
header('Content-Type: application/json');

// Corrected path to db_connect.php
require_once __DIR__ . '/../../db_connect.php';

// --- Security Check ---
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

// --- Get Input ---
$data = json_decode(file_get_contents('php://input'), true);
$return_id = $data['return_id'] ?? null;
$new_status = $data['status'] ?? null;

if (!$return_id || !in_array($new_status, ['approved', 'rejected'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input. Please provide a valid return ID and status.']);
    exit();
}

// --- Database Transaction ---
$conn->begin_transaction();

try {
    // Step 1: Update the status in the 'returns' table
    $stmt_update_return = $conn->prepare("UPDATE returns SET status = ? WHERE return_id = ?");
    if (!$stmt_update_return) {
        throw new Exception("Failed to prepare return update statement: " . $conn->error);
    }
    $stmt_update_return->bind_param("si", $new_status, $return_id);
    $stmt_update_return->execute();
    $stmt_update_return->close();

    // Step 2: Get the order details from the return request
    $stmt_get_order = $conn->prepare("SELECT order_id, user_id FROM returns WHERE return_id = ?");
    if (!$stmt_get_order) {
        throw new Exception("Failed to prepare order fetch statement: " . $conn->error);
    }
    $stmt_get_order->bind_param("i", $return_id);
    $stmt_get_order->execute();
    $result = $stmt_get_order->get_result();
    $return_info = $result->fetch_assoc();
    $stmt_get_order->close();

    if (!$return_info) {
        throw new Exception("Return ID not found.");
    }
    $order_id = $return_info['order_id'];
    $user_id = $return_info['user_id'];

    // Step 3: If approved, update order status and create a notification
    if ($new_status === 'approved') {
        $update_order_stmt = $conn->prepare("UPDATE product_orders SET status = 'refunded' WHERE order_id = ?");
        if (!$update_order_stmt) {
            throw new Exception("Failed to prepare order update statement: " . $conn->error);
        }
        $update_order_stmt->bind_param("i", $order_id);
        $update_order_stmt->execute();
        $update_order_stmt->close();
    }

    // Step 4: Create a notification for the user
    $notification_message = "Your return request for order #${order_id} has been updated to '${new_status}'.";
    $stmt_notify = $conn->prepare("INSERT INTO NOTIFICATION (USER_ID, MESSAGE) VALUES (?, ?)");
    if (!$stmt_notify) {
        throw new Exception("Failed to prepare notification statement: " . $conn->error);
    }
    $stmt_notify->bind_param("is", $user_id, $notification_message);
    $stmt_notify->execute();
    $stmt_notify->close();

    $conn->commit();
    echo json_encode(['success' => true, 'message' => "Return status successfully updated to {$new_status}."]);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    // Provide a more detailed error message for debugging
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}

$conn->close();
?>
