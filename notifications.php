<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch unread notifications for the user and universal notifications
$sql = "SELECT * FROM NOTIFICATION WHERE (USER_ID = ? OR USER_ID IS NULL) AND IS_READ = 0 ORDER BY CREATED_AT DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

// Mark notifications as read if requested (only for user-specific notifications)
if (isset($_GET['mark_as_read'])) {
    $notification_id = $_GET['mark_as_read'];
    $sql_update = "UPDATE NOTIFICATION SET IS_READ = 1 WHERE NOTIFICATION_ID = ? AND USER_ID = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("ii", $notification_id, $user_id);
    $stmt_update->execute();
}

echo json_encode($notifications);
?>