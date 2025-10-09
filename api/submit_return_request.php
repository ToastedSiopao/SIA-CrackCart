<?php
include "../error_handler.php";
header("Content-Type: application/json");
session_start();
include("../db_connect.php");
include "../log_function.php"; // Including the log function

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$order_item_id = isset($_POST['order_item_id']) ? intval($_POST['order_item_id']) : 0;
$reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';
$comments = isset($_POST['comments']) ? trim($_POST['comments']) : '';

if ($order_item_id === 0 || empty($reason)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Required fields are missing.']);
    exit();
}

// Use a transaction to ensure data integrity
$conn->begin_transaction();

try {
    // 1. Verify the item belongs to the user and get the order_id
    $stmt_verify = $conn->prepare("
        SELECT poi.order_id FROM product_order_items poi
        JOIN product_orders po ON poi.order_id = po.order_id
        WHERE poi.order_item_id = ? AND po.user_id = ?
    ");
    $stmt_verify->bind_param("ii", $order_item_id, $user_id);
    $stmt_verify->execute();
    $result_verify = $stmt_verify->get_result();
    if ($result_verify->num_rows === 0) {
        throw new Exception('Item not found or you do not have permission.');
    }
    $order_data = $result_verify->fetch_assoc();
    $order_id = $order_data['order_id'];

    // 2. Check if a return request already exists for this item
    // Corrected to check against `product_id` which stores the `order_item_id`
    $stmt_check = $conn->prepare("SELECT return_id FROM returns WHERE product_id = ?");
    $stmt_check->bind_param("i", $order_item_id);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows > 0) {
        throw new Exception('A return request for this item already exists.');
    }

    // 3. Insert the new return request
    // CORRECTED: Inserts into `product_id` instead of the non-existent `order_item_id` column.
    $full_reason = $reason . ($comments ? "; Comments: " . $comments : '');
    $stmt_insert = $conn->prepare("INSERT INTO returns (order_id, user_id, product_id, reason, status) VALUES (?, ?, ?, ?, 'pending')");
    $stmt_insert->bind_param("iiis", $order_id, $user_id, $order_item_id, $full_reason);
    $stmt_insert->execute();

    // Commit the transaction
    $conn->commit();

    // Log the action
    log_action('Return Request', "User ID: {$user_id} submitted a return request for Order Item ID: {$order_item_id}");

    echo json_encode(['status' => 'success', 'message' => 'Return request submitted successfully.', 'order_id' => $order_id]);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    log_action('Return Request Error', "Error submitting return for Order Item ID {$order_item_id}: " . $e->getMessage());
}

$conn->close();
?>