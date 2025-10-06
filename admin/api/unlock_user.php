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

if (!isset($input['user_id'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'User ID is required.']);
    exit;
}

$user_id_to_unlock = $input['user_id'];

// Prevent admin from unlocking themselves
if ($user_id_to_unlock == $_SESSION['user_id']) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'You cannot change your own account status.']);
    exit;
}

$conn->begin_transaction();

try {
    // Check if user exists and is locked
    $stmt_check = $conn->prepare("SELECT ACCOUNT_STATUS FROM USER WHERE USER_ID = ?");
    $stmt_check->bind_param("i", $user_id_to_unlock);
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('User not found.', 404);
    }

    $user = $result->fetch_assoc();
    $current_status = $user['ACCOUNT_STATUS'];
    $stmt_check->close();

    if ($current_status !== 'LOCKED') {
        throw new Exception('User is not locked.', 400);
    }

    // Update user status to ACTIVE and clear lock expiration
    $stmt_update = $conn->prepare("UPDATE USER SET ACCOUNT_STATUS = 'ACTIVE', LOCK_EXPIRES_AT = NULL WHERE USER_ID = ?");
    $stmt_update->bind_param("i", $user_id_to_unlock);

    if (!$stmt_update->execute()) {
        throw new Exception('Failed to unlock user account.', 500);
    }
    $stmt_update->close();

    // Create a notification for the user
    $notification_message = "Your account has been unlocked by an administrator.";
    create_notification($conn, $user_id_to_unlock, $notification_message);

    $conn->commit();
    echo json_encode(['status' => 'success', 'message' => "User account has been successfully unlocked."]);

} catch (Exception $e) {
    $conn->rollback();
    $errorCode = $e->getCode() > 0 ? $e->getCode() : 500;
    http_response_code($errorCode);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
?>