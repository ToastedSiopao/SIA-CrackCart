<?php
session_start();
require_once '../../db_connect.php';
require_once '../../notification_function.php'; // Include the notification function

// Admin-only access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Access denied.']);
    exit;
}

header('Content-Type: application/json');
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['user_id']) || !isset($input['role'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'User ID and role are required.']);
    exit;
}

$user_id_to_update = $input['user_id'];
$new_role = $input['role'];
$valid_roles = ['customer', 'admin', 'driver']; // As per schema

if (!in_array($new_role, $valid_roles)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid role specified.']);
    exit;
}

// Prevent admin from changing their own role
if ($user_id_to_update == $_SESSION['user_id']) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'You cannot change your own role.']);
    exit;
}

$conn->begin_transaction();

try {
    // Check if user exists
    $stmt_check = $conn->prepare("SELECT USER_ID FROM USER WHERE USER_ID = ?");
    $stmt_check->bind_param("i", $user_id_to_update);
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('User not found.', 404);
    }
    $stmt_check->close();

    // Update user role
    $stmt_update = $conn->prepare("UPDATE USER SET ROLE = ? WHERE USER_ID = ?");
    $stmt_update->bind_param("si", $new_role, $user_id_to_update);

    if (!$stmt_update->execute()) {
        throw new Exception('Failed to update user role.', 500);
    }
    $stmt_update->close();

    // Create a notification for the user
    $notification_message = "Your account role has been updated to '" . ucfirst($new_role) . "'.";
    create_notification($conn, $user_id_to_update, $notification_message);

    $conn->commit();
    echo json_encode(['status' => 'success', 'message' => "User role has been successfully updated to $new_role."]);

} catch (Exception $e) {
    $conn->rollback();
    $errorCode = $e->getCode() > 0 ? $e->getCode() : 500;
    http_response_code($errorCode);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
?>