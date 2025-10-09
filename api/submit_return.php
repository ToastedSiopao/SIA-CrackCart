<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json');

include('../db_connect.php');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in to submit a return.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$order_id = $_POST['order_id'] ?? null;
$order_item_id = $_POST['order_item_id'] ?? null;
$reason = $_POST['reason'] ?? null;
$image = $_FILES['return_image'] ?? null;

if (empty($order_id) || empty($order_item_id) || empty($reason)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required return information.']);
    exit;
}

$conn->begin_transaction();

try {
    // 1. Verify the order and item belong to the user
    $stmt = $conn->prepare("SELECT oi.*, p.PRODUCER_ID FROM product_order_items oi JOIN product_orders o ON oi.order_id = o.order_id JOIN PRICE p ON p.TYPE = oi.product_type WHERE o.order_id = ? AND o.user_id = ? AND oi.order_item_id = ?");
    $stmt->bind_param("iii", $order_id, $user_id, $order_item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        throw new Exception("Invalid order item or you do not have permission to return this item.", 403);
    }
    $order_item = $result->fetch_assoc();
    $stmt->close();

    // 2. Find the product_id (PRICE_ID) from the PRICE table
    // --- START FIX: Flexible Product ID Lookup ---
    $product_id = null;
    
    // First, try to find an exact match (producer, type, tray_size)
    $stmt = $conn->prepare("SELECT PRICE_ID FROM PRICE WHERE PRODUCER_ID = ? AND TYPE = ? AND TRAY_SIZE = ? LIMIT 1");
    $stmt->bind_param("isi", $order_item['producer_id'], $order_item['product_type'], $order_item['tray_size']);
    $stmt->execute();
    $prod_result = $stmt->get_result();
    if ($prod_row = $prod_result->fetch_assoc()) {
        $product_id = $prod_row['PRICE_ID'];
    }
    $stmt->close();

    // If no exact match, fall back to matching producer and type, ignoring tray_size
    if ($product_id === null) {
        $stmt = $conn->prepare("SELECT PRICE_ID FROM PRICE WHERE PRODUCER_ID = ? AND TYPE = ? LIMIT 1");
        $stmt->bind_param("is", $order_item['producer_id'], $order_item['product_type']);
        $stmt->execute();
        $prod_result = $stmt->get_result();
        if ($prod_row = $prod_result->fetch_assoc()) {
            $product_id = $prod_row['PRICE_ID'];
        }
        $stmt->close();
    }

    if ($product_id === null) {
        throw new Exception("Could not find a matching product ID to process the return.");
    }
    // --- END FIX ---

    // 3. Handle image upload if provided
    $image_path = null;
    if ($image && $image['error'] == UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/returns/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $ext = pathinfo($image['name'], PATHINFO_EXTENSION);
        $filename = uniqid('return_') . '.' . $ext;
        $image_path = 'uploads/returns/' . $filename;
        if (!move_uploaded_file($image['tmp_name'], '../' . $image_path)) {
            throw new Exception("Failed to upload return image.", 500);
        }
    }

    // 4. Insert into the returns table
    $stmt = $conn->prepare("INSERT INTO returns (order_id, order_item_id, user_id, product_id, reason, image_path) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiiss", $order_id, $order_item_id, $user_id, $product_id, $reason, $image_path);
    if (!$stmt->execute()) {
        throw new Exception("Failed to record the return request.", 500);
    }
    $stmt->close();

    $conn->commit();
    echo json_encode(['status' => 'success', 'message' => 'Your return request has been submitted successfully.']);

} catch (Exception $e) {
    $conn->rollback();
    $code = $e->getCode() >= 400 ? $e->getCode() : 500;
    http_response_code($code);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
?>