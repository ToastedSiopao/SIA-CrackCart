<?php
session_start();
header('Content-Type: application/json');
include '../db_connect.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'You must be logged in to apply a coupon.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$coupon_code = $data['coupon_code'] ?? '';
$user_id = $_SESSION['user_id'];

if (empty($coupon_code)) {
    echo json_encode(['success' => false, 'message' => 'Please provide a coupon code.']);
    exit;
}

if (isset($_SESSION['applied_coupon'])) {
    echo json_encode(['success' => false, 'message' => 'A coupon is already applied. Please remove it first.']);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM coupons WHERE coupon_code = ? AND user_id = ?");
$stmt->bind_param("si", $coupon_code, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($coupon = $result->fetch_assoc()) {
    if ($coupon['is_used']) {
        echo json_encode(['success' => false, 'message' => 'This coupon has already been used.']);
    } elseif (new DateTime() > new DateTime($coupon['expiry_date'])) {
        echo json_encode(['success' => false, 'message' => 'This coupon has expired.']);
    } else {
        $_SESSION['applied_coupon'] = [
            'coupon_id' => $coupon['coupon_id'],
            'coupon_code' => $coupon['coupon_code'],
            'discount_value' => $coupon['discount_value']
        ];
        echo json_encode(['success' => true, 'message' => 'Coupon applied successfully!']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid or expired coupon code.']);
}

$stmt->close();
$conn->close();
?>