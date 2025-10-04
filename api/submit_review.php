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
$order_id = $_POST['order_id'] ?? 0;
$product_id = $_POST['product_id'] ?? 0;
$rating = $_POST['rating'] ?? 0;
$review_text = trim($_POST['review_text'] ?? '');

// Basic validation
if (empty($order_id) || empty($product_id) || empty($rating)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields.']);
    exit();
}

if ($rating < 1 || $rating > 5) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid rating value.']);
    exit();
}

if ($conn) {
    try {
        // Verify that the user has purchased this product and the order is complete
        $stmt = $conn->prepare(
            "SELECT po.status, oi.product_id FROM product_orders po JOIN order_items oi ON po.order_id = oi.order_id WHERE po.order_id = ? AND po.user_id = ? AND oi.product_id = ?"
        );
        $stmt->bind_param("iii", $order_id, $user_id, $product_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'You are not authorized to review this product.']);
            exit();
        }

        $order = $result->fetch_assoc();
        $status = strtolower($order['status']);

        if ($status !== 'delivered' && $status !== 'completed') {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'You can only review products from completed orders.']);
            exit();
        }

        // Check if a review for this product in this order already exists
        $stmt_check = $conn->prepare("SELECT review_id FROM product_reviews WHERE user_id = ? AND order_id = ? AND product_id = ?");
        $stmt_check->bind_param("iii", $user_id, $order_id, $product_id);
        $stmt_check->execute();
        if ($stmt_check->get_result()->num_rows > 0) {
             http_response_code(409);
             echo json_encode(['status' => 'error', 'message' => 'You have already submitted a review for this product.']);
             exit();
        }

        // Insert the new review
        $stmt_insert = $conn->prepare("INSERT INTO product_reviews (user_id, order_id, product_id, rating, review_text) VALUES (?, ?, ?, ?, ?)");
        $stmt_insert->bind_param("iiiis", $user_id, $order_id, $product_id, $rating, $review_text);

        if ($stmt_insert->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Your review has been submitted successfully!']);
        } else {
            throw new Exception('Failed to save your review.');
        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    } finally {
        if (isset($stmt)) $stmt->close();
        if (isset($stmt_check)) $stmt_check->close();
        if (isset($stmt_insert)) $stmt_insert->close();
        $conn->close();
    }
} else {
    http_response_code(503);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
}
?>