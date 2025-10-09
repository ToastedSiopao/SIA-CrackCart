<?php
include "../db_connect.php";
header("Content-Type: application/json");

if (!isset($_GET['producer_id']) || !isset($_GET['product_type'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Producer ID and Product Type are required.']);
    exit;
}

$producer_id = intval($_GET['producer_id']);
$product_type = $_GET['product_type'];

try {
    // Fetch reviews and user names
    $stmt = $conn->prepare(
        "SELECT pr.rating, pr.review_text, pr.created_at, u.FIRST_NAME, u.LAST_NAME 
         FROM product_reviews pr
         JOIN USER u ON pr.user_id = u.USER_ID
         WHERE pr.order_id IN (
             SELECT DISTINCT order_id FROM product_order_items 
             WHERE producer_id = ? AND product_type = ?
         )
         ORDER BY pr.created_at DESC"
    );
    $stmt->bind_param("is", $producer_id, $product_type);
    $stmt->execute();
    $result = $stmt->get_result();
    $reviews = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Calculate average rating
    $total_rating = 0;
    foreach ($reviews as $review) {
        $total_rating += $review['rating'];
    }
    $average_rating = count($reviews) > 0 ? $total_rating / count($reviews) : 0;

    echo json_encode([
        'status' => 'success',
        'data' => [
            'reviews' => $reviews,
            'average_rating' => $average_rating,
            'review_count' => count($reviews)
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to fetch reviews: ' . $e->getMessage()]);
}

$conn->close();
?>
