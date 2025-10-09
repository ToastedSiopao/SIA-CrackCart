<?php
require_once __DIR__ . '../../../session_handler.php';
require_once __DIR__ . '../../../db_connect.php';
require_once __DIR__ . '../../../notification_function.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'] ?? null;
    $issue_description = $_POST['issue_description'] ?? null;
    $reported_by = $_SESSION['user_id'];

    if (!$order_id || !$issue_description) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Order ID and issue description are required']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO delivery_issues (order_id, reported_by, issue_description) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $order_id, $reported_by, $issue_description);

    if ($stmt->execute()) {
        // Get user_id from the order
        $order_stmt = $conn->prepare("SELECT user_id FROM product_orders WHERE order_id = ?");
        $order_stmt->bind_param("i", $order_id);
        $order_stmt->execute();
        $order_result = $order_stmt->get_result();
        $order_data = $order_result->fetch_assoc();
        $user_id = $order_data['user_id'];

        // Create notification for the user
        $notification_message = "An issue has been reported with your order #$order_id: $issue_description";
        create_notification($user_id, $notification_message);

        echo json_encode(['success' => true, 'message' => 'Issue reported successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to report issue']);
    }
}
?>