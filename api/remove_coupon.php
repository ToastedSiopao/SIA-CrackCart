<?php
session_start();
header('Content-Type: application/json');

// 1. Security Check: Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'You must be logged in to perform this action.']);
    exit;
}

// 2. Remove Coupon from Session
if (isset($_SESSION['applied_coupon'])) {
    unset($_SESSION['applied_coupon']);
    echo json_encode(['success' => true, 'message' => 'Coupon removed successfully.']);
} else {
    // This case is unlikely to be hit in normal flow, but is good practice
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'No coupon was applied to your session.']);
}
?>
