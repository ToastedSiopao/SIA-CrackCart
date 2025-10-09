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
    // 1. Lock the vehicle row and check its status
    $stmt_check_vehicle = $conn->prepare("SELECT status FROM Vehicle WHERE vehicle_id = ? FOR UPDATE");
    $stmt_check_vehicle->bind_param("i", $vehicle_id);
    $stmt_check_vehicle->execute();
    $result_vehicle = $stmt_check_vehicle->get_result();
    if ($result_vehicle->num_rows === 0) {
        throw new Exception("Vehicle not found.", 404);
    }
    $vehicle = $result_vehicle->fetch_assoc();
    if ($vehicle['status'] !== 'available') {
        throw new Exception("Vehicle is not available. Current status: " . htmlspecialchars($vehicle['status']), 409);
    }
    $stmt_check_vehicle->close();

    // 2. Find the driver assigned to the vehicle
    $stmt_driver = $conn->prepare("SELECT driver_id FROM Driver WHERE vehicle_id = ?");
    $stmt_driver->bind_param("i", $vehicle_id);
    $stmt_driver->execute();
    $result_driver = $stmt_driver->get_result();
    if ($result_driver->num_rows === 0) {
        throw new Exception("No driver is assigned to the selected vehicle.", 409);
    }
    $driver = $result_driver->fetch_assoc();
    $driver_id = $driver['driver_id'];
    $stmt_driver->close();

    // 3. Assign vehicle to the order
    $stmt_order = $conn->prepare("UPDATE product_orders SET vehicle_id = ? WHERE order_id = ?");
    $stmt_order->bind_param("ii", $vehicle_id, $order_id);
    $stmt_order->execute();
    if ($stmt_order->affected_rows === 0) {
        throw new Exception("Order not found or could not be updated.", 404);
    }
    $stmt_order->close();

    // 4. Update order status to 'shipped'
    $stmt_order_status = $conn->prepare("UPDATE product_orders SET status = 'shipped' WHERE order_id = ?");
    $stmt_order_status->bind_param("i", $order_id);
    $stmt_order_status->execute();
    if ($stmt_order_status->affected_rows === 0) {
        throw new Exception("Failed to update order status.", 500);
    }
    $stmt_order_status->close();

    // 5. Update vehicle status to 'in-transit'
    $stmt_vehicle = $conn->prepare("UPDATE Vehicle SET status = 'in-transit' WHERE vehicle_id = ?");
    $stmt_vehicle->bind_param("i", $vehicle_id);
    $stmt_vehicle->execute();
    if ($stmt_vehicle->affected_rows === 0) {
        throw new Exception("Failed to update vehicle status.", 500);
    }
    $stmt_vehicle->close();

    // 6. Create a new record in Delivery_Assignment
    $stmt_assign = $conn->prepare("INSERT INTO Delivery_Assignment (booking_id, driver_id, vehicle_id, status, assigned_at) VALUES (?, ?, ?, 'assigned', NOW())");
    $stmt_assign->bind_param("iii", $order_id, $driver_id, $vehicle_id);
    $stmt_assign->execute();
    if ($stmt_assign->affected_rows === 0) {
        throw new Exception("Failed to create delivery assignment record.", 500);
    }
    $stmt_assign->close();

    $conn->commit();

    log_action($_SESSION['user_id'], 'Vehicle Assignment', "Assigned vehicle #{$vehicle_id} to order #{$order_id}, assigned to driver #{$driver_id}.");

    echo json_encode(['status' => 'success', 'message' => "Vehicle #{$vehicle_id} assigned to order #{$order_id}, and delivery assignment created for driver #{$driver_id}."]);

} catch (Exception $e) {
    $conn->rollback();
    $http_code = ($e->getCode() >= 400) ? $e->getCode() : 500;
    http_response_code($http_code);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
?>