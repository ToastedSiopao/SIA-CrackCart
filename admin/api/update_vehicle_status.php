<?php
session_start();
require_once '../../db_connect.php';
require_once '../../log_function.php'; // Assuming you have a logging function

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Access denied.']);
    exit;
}

header('Content-Type: application/json');
$input = json_decode(file_get_contents('php://input'), true);

$vehicle_id = filter_var($input['vehicle_id'] ?? null, FILTER_VALIDATE_INT);
$status = $input['status'] ?? null;

if (!$vehicle_id || !$status) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Vehicle ID and status are required.']);
    exit;
}

// --- FUNCTIONALITY FIX: Added 'maintenance' to align with the frontend UI ---
$allowed_statuses = ['available', 'in-transit', 'maintenance'];
if (!in_array($status, $allowed_statuses)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => "Invalid status '" . htmlspecialchars($status) . "' provided."]);
    exit;
}

try {
    $stmt = $conn->prepare("UPDATE Vehicle SET status = ? WHERE vehicle_id = ?");
    $stmt->bind_param('si', $status, $vehicle_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            log_action($_SESSION['user_id'], 'Vehicle Status Update', "Admin set vehicle #{$vehicle_id} to {$status}.");
            echo json_encode(['status' => 'success', 'message' => "Vehicle #{$vehicle_id} status updated to {$status}."]);
        } else {
            http_response_code(404); // Not Found
            echo json_encode(['status' => 'error', 'message' => "Vehicle #{$vehicle_id} not found or status is already {$status}."]);
        }
    } else {
        throw new Exception('Database execution failed.');
    }
} catch (Exception $e) {
    http_response_code(500);
    log_action($_SESSION['user_id'], 'Vehicle Status Error', "Failed to set vehicle #{$vehicle_id} to {$status}. Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'An internal server error occurred.']);
}

$stmt->close();
$conn->close();
?>
