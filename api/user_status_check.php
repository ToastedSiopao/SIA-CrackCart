<?php
session_start();
header("Content-Type: application/json");
include("../db_connect.php");

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // Fetch user status and lock expiry date
    $stmt = $conn->prepare("SELECT ACCOUNT_STATUS, LOCK_EXPIRES_AT FROM USER WHERE USER_ID = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'User not found.']);
        exit();
    }

    $user = $result->fetch_assoc();
    $status = $user['ACCOUNT_STATUS'];
    $expires_at = $user['LOCK_EXPIRES_AT'];

    // Check if the account is locked
    if ($status === 'LOCKED') {
        $now = new DateTime();
        $expiry_date = new DateTime($expires_at);

        if ($now < $expiry_date) {
            // The lock is still active.
            $lock_expiry_formatted = date('F j, Y, g:i a', strtotime($expires_at));
            $error_message = "Your account is temporarily locked due to excessive order cancellations. You will be able to place orders again after {$lock_expiry_formatted}.";
            
            http_response_code(403); // Forbidden
            echo json_encode(['status' => 'locked', 'message' => $error_message]);
            exit();
        } else {
            // The lock has expired, so we should unlock the account.
            $unlock_stmt = $conn->prepare("UPDATE USER SET ACCOUNT_STATUS = 'ACTIVE', LOCK_EXPIRES_AT = NULL WHERE USER_ID = ?");
            $unlock_stmt->bind_param("i", $user_id);
            $unlock_stmt->execute();
            $unlock_stmt->close();
        }
    }

    // If we reach here, the account is active.
    echo json_encode(['status' => 'success', 'message' => 'Account is active.']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'An internal server error occurred.']);
}

$conn->close();
?>