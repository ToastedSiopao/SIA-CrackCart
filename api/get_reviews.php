<?php
header('Content-Type: application/json');
include('../db_connect.php');
include('../error_handler.php');

if (!isset($_GET['product_type'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Product type is required.']);
    exit();
}

$product_type = trim($_GET['product_type']);

try {
    // Get reviews and user info
    $stmt_reviews = $conn->prepare(
        "SELECT r.rating, r.review_text, r.created_at, u.username
         FROM product_reviews r
         JOIN users u ON r.user_id = u.user_id
         WHERE r.product_type = ?
         ORDER BY r.created_at DESC"
    );
    $stmt_reviews->bind_param("s", $product_type);
    $stmt_reviews->execute();
    $result_reviews = $stmt_reviews->get_result();
    $reviews = $result_reviews->fetch_all(MYSQLI_ASSOC);

    // Get aggregate data (average rating, total reviews)
    $stmt_agg = $conn->prepare(
        "SELECT AVG(rating) as avg_rating, COUNT(review_id) as total_reviews
         FROM product_reviews
         WHERE product_type = ?"
    );
    $stmt_agg->bind_param("s", $product_type);
    $stmt_agg->execute();
    $result_agg = $stmt_agg->get_result();
    $aggregation = $result_agg->fetch_assoc();

    echo json_encode([
        'status' => 'success',
        'data' => [
            'reviews' => $reviews,
            'average_rating' => $aggregation['avg_rating'] ? floatval($aggregation['avg_rating']) : 0,
            'total_reviews' => intval($aggregation['total_reviews'])
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

?>