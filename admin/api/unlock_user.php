<?php
session_start();
require_once '../../db_connect.php';

header('Content-Type: application/json');

// Check if the user is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Forbidden: You do not have permission to perform this action.']);
    exit;
}

// Get the input from the request body
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['user_id']) || !is_numeric($input['user_id'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid user ID provided.']);
    exit;
}

$user_id_to_unlock = intval($input['user_id']);

// Prepare the SQL statement to prevent SQL injection
$query = "UPDATE USER SET ACCOUNT_STATUS = 'ACTIVE', LOCK_EXPIRES_AT = NULL WHERE USER_ID = ?";

$stmt = $conn->prepare($query);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: Could not prepare the statement.']);
    exit;
}

$stmt->bind_param("i", $user_id_to_unlock);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['status' => 'success', 'message' => "User account #{$user_id_to_unlock} has been successfully unlocked."]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'User account not found or no changes were made.']);
    }
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to execute the unlock operation.']);
}

$stmt->close();
$conn->close();
?>
