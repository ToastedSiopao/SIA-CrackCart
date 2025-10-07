<?php
session_start();
header('Content-Type: application/json');
require_once '../db_connect.php';

// 1. Security Check: Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'You must be logged in to apply a coupon.']);
    exit;
}

// 2. Input Validation
$data = json_decode(file_get_contents('php://input'), true);
$coupon_code = trim($data['coupon_code'] ?? '');

if (empty($coupon_code)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Please enter a coupon code.']);
    exit;
}

// 3. Coupon Verification Logic
try {
    // Prepare a query to find the coupon
    $stmt = $conn->prepare("SELECT coupon_id, user_id, discount_value, is_used FROM coupons WHERE coupon_code = ?");
    $stmt->bind_param('s', $coupon_code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        http_response_code(404); // Not Found
        echo json_encode(['success' => false, 'message' => 'Coupon code not found.']);
        exit;
    }

    $coupon = $result->fetch_assoc();

    // Check if the coupon is assigned to the current user
    if ($coupon['user_id'] != $_SESSION['user_id']) {
        http_response_code(403); // Forbidden
        echo json_encode(['success' => false, 'message' => 'This coupon is not valid for your account.']);
        exit;
    }

    // Check if the coupon has already been used
    if ($coupon['is_used'] == 1) {
        http_response_code(409); // Conflict
        echo json_encode(['success' => false, 'message' => 'This coupon has already been used.']);
        exit;
    }

    // --- Success Case ---
    // Store the valid coupon details in the session to be used at checkout
    $_SESSION['applied_coupon'] = [
        'coupon_code' => $coupon_code,
        'discount_value' => $coupon['discount_value']
    ];

    echo json_encode([
        'success' => true, 
        'message' => 'Coupon applied successfully!', 
        'discount_value' => $coupon['discount_value']
    ]);

} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'message' => 'An internal error occurred while applying the coupon.']);
} finally {
    if (isset($stmt)) $stmt->close();
    $conn->close();
}
?>
