<?php
require_once __DIR__ . '../../session_handler.php';
require_once __DIR__ . '../../db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'driver') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

$driver_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $order_id = $data['order_id'] ?? null;
    $status = $data['status'] ?? null;

    if (!$order_id || !$status) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Order ID and status are required']);
        exit;
    }

    // Verify that the order is assigned to the driver
    $verify_stmt = $conn->prepare("SELECT 1 FROM product_orders WHERE order_id = ? AND vehicle_id IN (SELECT vehicle_id FROM Vehicle WHERE driver_id = ?)");
    $verify_stmt->bind_param("ii", $order_id, $driver_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();

    if ($verify_result->num_rows === 0) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'You are not authorized to update this order.']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE product_orders SET status = ? WHERE order_id = ?");
    $stmt->bind_param("si", $status, $order_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Order status updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update order status']);
    }
}
?>