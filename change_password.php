<?php
session_start();
include("db_connect.php");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';

// Get current password from DB
$sql = "SELECT PASSWORD FROM USER WHERE USER_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user && password_verify($current_password, $user['PASSWORD'])) {
    // Hash the new password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    $sql = "UPDATE USER SET PASSWORD = ? WHERE USER_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $hashed_password, $user_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Password changed successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to change password']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid current password']);
}
?>