<?php
session_start();
header('Content-Type: application/json');
require_once '../db_connect.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in to leave a review.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$order_item_id = isset($_POST['order_item_id']) ? intval($_POST['order_item_id']) : 0;
$rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
$review_text = isset($_POST['review_text']) ? trim($_POST['review_text']) : '';

if ($order_item_id <= 0 || $rating < 1 || $rating > 5) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid data provided. Please select a rating.']);
    exit;
}

$conn->begin_transaction();

try {
    // 1. Get order item details and verify ownership and order status
    $item_query = "
        SELECT 
            oi.product_type, oi.order_id, po.status
        FROM 
            product_order_items oi
        JOIN 
            product_orders po ON oi.order_id = po.order_id
        WHERE 
            oi.order_item_id = ? AND po.user_id = ?
    ";
    $stmt_item = $conn->prepare($item_query);
    $stmt_item->bind_param("ii", $order_item_id, $user_id);
    $stmt_item->execute();
    $item_result = $stmt_item->get_result();

    if ($item_result->num_rows === 0) {
        throw new Exception('Order item not found or you do not have permission to review it.', 404);
    }
    $item_data = $item_result->fetch_assoc();
    $stmt_item->close();

    if (strtolower($item_data['status']) !== 'delivered') {
        throw new Exception('You can only review items from delivered orders.', 403);
    }

    // 2. Check if a review for this specific order item already exists
    $review_check_stmt = $conn->prepare("SELECT review_id FROM product_reviews WHERE order_item_id = ?");
    $review_check_stmt->bind_param("i", $order_item_id);
    $review_check_stmt->execute();
    if ($review_check_stmt->get_result()->num_rows > 0) {
        throw new Exception('You have already reviewed this item.', 409);
    }
    $review_check_stmt->close();

    // 3. Insert the new review
    $product_type = $item_data['product_type'];
    $order_id = $item_data['order_id'];
    $insert_stmt = $conn->prepare("INSERT INTO product_reviews (user_id, order_id, order_item_id, product_type, rating, review_text) VALUES (?, ?, ?, ?, ?, ?)");
    $insert_stmt->bind_param("iiisss", $user_id, $order_id, $order_item_id, $product_type, $rating, $review_text);

    if (!$insert_stmt->execute()) {
        throw new Exception('An error occurred while submitting your review.', 500);
    }
    $insert_stmt->close();

    // 4. Mark the order item as reviewed
    $update_stmt = $conn->prepare("UPDATE product_order_items SET is_reviewed = 1 WHERE order_item_id = ?");
    $update_stmt->bind_param("i", $order_item_id);
    $update_stmt->execute();
    $update_stmt->close();

    $conn->commit();
    echo json_encode(['status' => 'success', 'message' => 'Thank you for your review!']);

} catch (Exception $e) {
    $conn->rollback();
    $code = $e->getCode() >= 400 ? $e->getCode() : 500;
    http_response_code($code);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
?>