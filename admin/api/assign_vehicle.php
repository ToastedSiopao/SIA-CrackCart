<?php
session_start();
require_once '../../db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Access denied.']);
    exit;
}

header('Content-Type: application/json');
$input = json_decode(file_get_contents('php://input'), true);

$order_id = $input['order_id'] ?? null;
$vehicle_id = $input['vehicle_id'] ?? null;

if (!$order_id || !$vehicle_id) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Order ID and Vehicle ID are required.']);
    exit;
}

$conn->begin_transaction();

try {
    // 1. Check if vehicle is available
    $stmt_check = $conn->prepare("SELECT status FROM vehicles WHERE id = ?");
    $stmt_check->bind_param("i", $vehicle_id);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    if ($result->num_rows === 0) {
        throw new Exception("Vehicle not found.");
    }
    $vehicle = $result->fetch_assoc();
    if ($vehicle['status'] !== 'standby') {
        throw new Exception("Vehicle is not available. Current status: " . $vehicle['status']);
    }
    $stmt_check->close();

    // 2. Assign vehicle to order
    $stmt_order = $conn->prepare("UPDATE product_orders SET vehicle_id = ? WHERE order_id = ?");
    $stmt_order->bind_param("ii", $vehicle_id, $order_id);
    if (!$stmt_order->execute()) {
        throw new Exception("Failed to assign vehicle to order.");
    }
    $stmt_order->close();

    // 3. Update vehicle status to 'in-transit'
    $stmt_vehicle = $conn->prepare("UPDATE vehicles SET status = 'in-transit' WHERE id = ?");
    $stmt_vehicle->bind_param("i", $vehicle_id);
    if (!$stmt_vehicle->execute()) {
        throw new Exception("Failed to update vehicle status.");
    }
    $stmt_vehicle->close();

    $conn->commit();
    echo json_encode(['status' => 'success', 'message' => 'Vehicle assigned successfully.']);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
?>
