<?php
session_start();
require_once '../db_connect.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in to view your coupons.']);
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT coupon_code, discount_value, expiry_date FROM coupons WHERE user_id = ? AND is_used = 0 AND expiry_date >= CURDATE()");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$coupons = [];
while ($row = $result->fetch_assoc()) {
    $coupons[] = $row;
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode(['status' => 'success', 'data' => $coupons]);
?>
