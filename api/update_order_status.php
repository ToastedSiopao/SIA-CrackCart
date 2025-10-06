<?php
session_start();
require_once '../db_connect.php'; 

header('Content-Type: application/json');

// Admin authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Forbidden: You do not have permission.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

// Input validation
$order_id = isset($input['order_id']) ? intval($input['order_id']) : 0;
$new_status = isset($input['status']) ? trim($input['status']) : '';

if ($order_id <= 0 || empty($new_status)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid input. Order ID and new status are required.']);
    exit;
}

// Check if the status is valid
$allowed_statuses = ['pending', 'paid', 'processing', 'shipped', 'delivered', 'cancelled'];
if (!in_array($new_status, $allowed_statuses)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid status value provided.']);
    exit;
}

try {
    // Check if the order exists
    $check_stmt = $conn->prepare("SELECT status FROM product_orders WHERE order_id = ?");
    $check_stmt->bind_param('i', $order_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => "Order with ID {$order_id} not found."]);
        exit;
    }
    $current_order = $result->fetch_assoc();
    $check_stmt->close();

    // Prevent marking a cancelled order as delivered
    if ($current_order['status'] === 'cancelled' && $new_status === 'delivered') {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Cannot mark a cancelled order as delivered.']);
        exit;
    }
    
    // Prepare and execute the update
    $stmt = $conn->prepare("UPDATE product_orders SET status = ? WHERE order_id = ?");
    $stmt->bind_param('si', $new_status, $order_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['status' => 'success', 'message' => "Order #{$order_id} has been updated to {$new_status}."]);
        } else {
            echo json_encode(['status' => 'success', 'message' => 'The order status was already set to the requested value.']);
        }
    } else {
        throw new Exception("Failed to update the order status in the database.");
    }
    $stmt->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
} finally {
    if (isset($conn) && $conn) {
        $conn->close();
    }
}
?>