<?php
require_once '../../db_connect.php';
require_once '../../session_handler.php';

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

// Prevent admin from locking/unlocking themselves
if ($user_id_to_unlock == $_SESSION['user_id']) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Admin users cannot change their own lock status.']);
    exit;
}

$conn->begin_transaction();

try {
    // Check if user exists and is actually locked
    $stmt_check = $conn->prepare("SELECT status FROM users WHERE id = ?");
    $stmt_check->bind_param("i", $user_id_to_unlock);
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('User not found.', 404);
    }

    $user = $result->fetch_assoc();
    $current_status = $user['status'];
    $stmt_check->close();

    if ($current_status !== 'LOCKED') {
        throw new Exception('User is not locked.', 400);
    }

    // Update user status to ACTIVE
    $stmt_update = $conn->prepare("UPDATE users SET status = 'ACTIVE' WHERE id = ?");
    $stmt_update->bind_param("i", $user_id_to_unlock);

    if (!$stmt_update->execute()) {
        throw new Exception('Failed to unlock user account.', 500);
    }
    $stmt_update->close();

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