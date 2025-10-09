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
$plate_no = trim($input['plate_no'] ?? '');
$type = trim($input['type'] ?? '');

if (empty($plate_no) || empty($type)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Plate number and vehicle type are required.']);
    exit;
}

// Basic validation for plate number format
if (!preg_match('/^[A-Z0-9- ]+$/', $plate_no)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid plate number format.']);
    exit;
}

try {
    // The status of a new vehicle should default to 'available'
    $stmt = $conn->prepare("INSERT INTO Vehicle (plate_no, type, status) VALUES (?, ?, 'available')");
    $stmt->bind_param('ss', $plate_no, $type);

    if ($stmt->execute()) {
        $new_vehicle_id = $conn->insert_id;
        log_action($_SESSION['user_id'], 'Add Vehicle', "Admin added new vehicle #{$new_vehicle_id} ({$type} - {$plate_no}).");
        echo json_encode(['status' => 'success', 'message' => 'New vehicle added successfully.', 'vehicle_id' => $new_vehicle_id]);
    } else {
        // Handle potential duplicate entry for plate_no
        if ($conn->errno === 1062) {
             http_response_code(409); // Conflict
             echo json_encode(['status' => 'error', 'message' => "A vehicle with plate number '" . htmlspecialchars($plate_no) . "' already exists."]);
        } else {
            throw new Exception('Database execution failed.');
        }
    }
} catch (Exception $e) {
    http_response_code(500);
    log_action($_SESSION['user_id'], 'Add Vehicle Error', "Failed to add vehicle. Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'An internal server error occurred while adding the vehicle.']);
}

$stmt->close();
$conn->close();
?>
