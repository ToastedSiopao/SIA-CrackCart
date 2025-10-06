<?php
session_start();
include '../db_connect.php';
include '../log_function.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Authentication required. Please log in.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$order_item_id = isset($_POST['order_item_id']) ? intval($_POST['order_item_id']) : 0;
$reason = trim($_POST['reason'] ?? '');

if ($order_item_id === 0 || empty($reason)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Please provide an item and a reason for the return.']);
    exit;
}

$conn->begin_transaction();

try {
    // 1. Verify item eligibility and get the order_id
    // CORRECTED QUERY: Using uppercase for USER_ID and STATUS to match the database schema.
    $verify_stmt = $conn->prepare(
        "SELECT po.order_id 
         FROM product_order_items poi 
         JOIN product_orders po ON poi.order_id = po.order_id 
         WHERE poi.order_item_id = ? AND po.USER_ID = ? AND po.STATUS = 'Delivered'"
    );
    $verify_stmt->bind_param("ii", $order_item_id, $user_id);
    $verify_stmt->execute();
    $result = $verify_stmt->get_result();
    if ($result->num_rows == 0) {
        throw new Exception('This item is not eligible for return. It might not belong to you or the order has not been delivered yet.');
    }
    $order_data = $result->fetch_assoc();
    $order_id = $order_data['order_id']; // Capture the order_id
    $verify_stmt->close();

    // 2. Check if a return request for this specific item already exists
    $check_stmt = $conn->prepare("SELECT return_id FROM returns WHERE order_item_id = ?");
    $check_stmt->bind_param("i", $order_item_id);
    $check_stmt->execute();
    $check_stmt->store_result();
    if ($check_stmt->num_rows > 0) {
        throw new Exception('A return request has already been submitted for this item.');
    }
    $check_stmt->close();

    // 3. Insert the new return request
    $insert_stmt = $conn->prepare("INSERT INTO returns (order_id, order_item_id, reason, status) VALUES (?, ?, ?, 'Requested')");
    $insert_stmt->bind_param("iis", $order_id, $order_item_id, $reason);
    
    if (!$insert_stmt->execute()) {
        throw new Exception('Failed to save your return request due to a database error.');
    }
    $insert_stmt->close();

    log_action($user_id, 'Return Requested', "User requested return for order_item_id: {$order_item_id}");

    $conn->commit();

    echo json_encode(['status' => 'success', 'message' => 'Your return request has been submitted successfully!']);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
?>
