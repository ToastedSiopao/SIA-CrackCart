<?php
session_start();
include '../db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in to request a return.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$reason = trim($_POST['reason'] ?? '');

if ($order_id === 0 || $product_id === 0 || empty($reason)) {
    echo json_encode(['status' => 'error', 'message' => 'Please fill out all fields.']);
    exit;
}

// 1. Verify the order belongs to the user and the product is in the order
$stmt_verify = $conn->prepare("SELECT COUNT(*) FROM orders o JOIN order_items oi ON o.order_id = oi.order_id WHERE o.user_id = ? AND o.order_id = ? AND oi.product_id = ?");
$stmt_verify->bind_param("iii", $user_id, $order_id, $product_id);
$stmt_verify->execute();
$stmt_verify->bind_result($count);
$stmt_verify->fetch();
$stmt_verify->close();

if ($count == 0) {
    echo json_encode(['status' => 'error', 'message' => 'You are not authorized to return this product.']);
    exit;
}

// 2. Check if a return request for this product and order already exists
$stmt_check = $conn->prepare("SELECT return_id FROM returns WHERE user_id = ? AND order_id = ? AND product_id = ?");
$stmt_check->bind_param("iii", $user_id, $order_id, $product_id);
$stmt_check->execute();
$stmt_check->store_result();

if ($stmt_check->num_rows > 0) {
    $stmt_check->close();
    echo json_encode(['status' => 'error', 'message' => 'You have already submitted a return request for this item.']);
    exit;
}
$stmt_check->close();

// 3. Insert the new return request
try {
    $stmt_insert = $conn->prepare("INSERT INTO returns (user_id, order_id, product_id, reason) VALUES (?, ?, ?, ?)");
    $stmt_insert->bind_param("iiis", $user_id, $order_id, $product_id, $reason);
    
    if ($stmt_insert->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Your return request has been submitted successfully! You will be notified once it is reviewed.']);
    } else {
        throw new Exception('Failed to save your request.');
    }
    $stmt_insert->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'A database error occurred: ' . $e->getMessage()]);
}

$conn->close();
?>
