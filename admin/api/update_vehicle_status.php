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

$vehicle_id = $input['vehicle_id'] ?? null;
$status = $input['status'] ?? null;

if (!$vehicle_id || !$status) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Vehicle ID and status are required.']);
    exit;
}

$allowed_statuses = ['available', 'in-transit'];
if (!in_array($status, $allowed_statuses)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid status provided.']);
    exit;
}

$stmt = $conn->prepare("UPDATE Vehicle SET status = ? WHERE vehicle_id = ?");
$stmt->bind_param('si', $status, $vehicle_id);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Vehicle status updated successfully.']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to update vehicle status.']);
}

$stmt->close();
$conn->close();
?>
