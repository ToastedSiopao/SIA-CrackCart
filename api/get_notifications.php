<?php
session_start();
require_once '../db_connect.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

header('Content-Type: application/json');

// Fetch notifications
$query_notif = "SELECT NOTIFICATION_ID, MESSAGE, IS_READ, CREATED_AT FROM NOTIFICATION WHERE USER_ID = ? ORDER BY CREATED_AT DESC LIMIT 10";
$stmt_notif = $conn->prepare($query_notif);
$stmt_notif->bind_param("i", $user_id);
$stmt_notif->execute();
$result_notif = $stmt_notif->get_result();
$notifications = $result_notif->fetch_all(MYSQLI_ASSOC);
$stmt_notif->close();

// Fetch unread count
$query_count = "SELECT COUNT(*) as unread_count FROM NOTIFICATION WHERE USER_ID = ? AND IS_READ = 0";
$stmt_count = $conn->prepare($query_count);
$stmt_count->bind_param("i", $user_id);
$stmt_count->execute();
$result_count = $stmt_count->get_result();
$unread_count = $result_count->fetch_assoc()['unread_count'];
$stmt_count->close();

$conn->close();

echo json_encode(['notifications' => $notifications, 'unread_count' => $unread_count]);
?>