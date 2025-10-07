<?php
session_start();
require_once '../../db_connect.php';
require_once '../../log_function.php';

// Check user authentication and role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Access denied.']);
    exit;
}

header('Content-Type: application/json');
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
$order_id = filter_var($input['order_id'] ?? null, FILTER_VALIDATE_INT);
$vehicle_id = filter_var($input['vehicle_id'] ?? null, FILTER_VALIDATE_INT);

if (!$order_id || !$vehicle_id) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Valid Order ID and Vehicle ID are required.']);
    exit;
}

$conn->begin_transaction();

try {
    // 1. Lock the vehicle row to prevent race conditions
    $stmt_check = $conn->prepare("SELECT status FROM Vehicle WHERE vehicle_id = ? FOR UPDATE");
    $stmt_check->bind_param("i", $vehicle_id);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    if ($result->num_rows === 0) {
        throw new Exception("Vehicle not found.", 404);
    }
    $vehicle = $result->fetch_assoc();
    if ($vehicle['status'] !== 'available') {
        throw new Exception("Vehicle is not available. Current status: " . htmlspecialchars($vehicle['status']), 409);
    }
    $stmt_check->close();

    // 2. Assign vehicle to the order
    $stmt_order = $conn->prepare("UPDATE product_orders SET vehicle_id = ? WHERE order_id = ?");
    $stmt_order->bind_param("ii", $vehicle_id, $order_id);
    $stmt_order->execute();
    if ($stmt_order->affected_rows === 0) {
        throw new Exception("Order not found or could not be updated.", 404);
    }
    $stmt_order->close();

    // 3. --- FIX: Update order status to 'shipped' for data consistency ---
    $stmt_order_status = $conn->prepare("UPDATE product_orders SET order_status = 'shipped' WHERE order_id = ?");
    $stmt_order_status->bind_param("i", $order_id);
    $stmt_order_status->execute();
    if ($stmt_order_status->affected_rows === 0) {
        // This case is unlikely if the previous update succeeded, but it's good practice to check
        throw new Exception("Failed to update order status.", 500);
    }
    $stmt_order_status->close();

    // 4. Update vehicle status to 'in-transit'
    $stmt_vehicle = $conn->prepare("UPDATE Vehicle SET status = 'in-transit' WHERE vehicle_id = ?");
    $stmt_vehicle->bind_param("i", $vehicle_id);
    $stmt_vehicle->execute();
    if ($stmt_vehicle->affected_rows === 0) {
        // This case is also unlikely but included for robustness
        throw new Exception("Failed to update vehicle status.", 500);
    }
    $stmt_vehicle->close();

    $conn->commit();

    log_action($_SESSION['user_id'], 'Vehicle Assignment', "Assigned vehicle #{$vehicle_id} to order #{$order_id}.");

    echo json_encode(['status' => 'success', 'message' => "Vehicle #{$vehicle_id} assigned to order #{$order_id} and status set to shipped."]);

} catch (Exception $e) {
    $conn->rollback();
    $http_code = ($e->getCode() >= 400) ? $e->getCode() : 500;
    http_response_code($http_code);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
?>
