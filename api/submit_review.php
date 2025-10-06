<?php
session_start();
header('Content-Type: application/json');
include('../db_connect.php');
include('../error_handler.php');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = $_POST['order_id'] ?? 0;
$product_type = trim($_POST['product_type'] ?? '');
$rating = $_POST['rating'] ?? 0;
$review_text = trim($_POST['review_text'] ?? '');

// Basic validation
if (empty($order_id) || empty($product_type) || empty($rating)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields.']);
    exit();
}

if ($rating < 1 || $rating > 5) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid rating value.']);
    exit();
}

try {
    // Check if the user has purchased this product in the specified order
    $stmt_check = $conn->prepare(
        "SELECT od.id FROM order_details od
         JOIN orders o ON od.order_id = o.order_id
         WHERE o.order_id = ? AND o.user_id = ? AND od.product_type = ?"
    );
    $stmt_check->bind_param("iis", $order_id, $user_id, $product_type);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows === 0) {
        throw new Exception('You can only review products you have purchased.');
    }

    // Check if a review already exists for this product in this order
    $stmt_exists = $conn->prepare("SELECT review_id FROM product_reviews WHERE user_id = ? AND order_id = ? AND product_type = ?");
    $stmt_exists->bind_param("iis", $user_id, $order_id, $product_type);
    $stmt_exists->execute();
    $result_exists = $stmt_exists->get_result();

    if ($result_exists->num_rows > 0) {
        // Update existing review
        $review = $result_exists->fetch_assoc();
        $review_id = $review['review_id'];
        $stmt_update = $conn->prepare("UPDATE product_reviews SET rating = ?, review_text = ? WHERE review_id = ?");
        $stmt_update->bind_param("isi", $rating, $review_text, $review_id);
        if ($stmt_update->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Your review has been updated successfully!']);
        } else {
            throw new Exception('Failed to update your review.');
        }
    } else {
        // Insert the new review
        $stmt_insert = $conn->prepare("INSERT INTO product_reviews (user_id, order_id, product_type, rating, review_text) VALUES (?, ?, ?, ?, ?)");
        $stmt_insert->bind_param("iisis", $user_id, $order_id, $product_type, $rating, $review_text);

        if ($stmt_insert->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Your review has been submitted successfully!']);
        } else {
            throw new Exception('Failed to save your review.');
        }
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>