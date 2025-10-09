<?php
session_start();
require_once '../db_connect.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $notification_id = $data['notification_id'] ?? null;

    if ($notification_id) {
        $sql = "UPDATE NOTIFICATION SET IS_READ = 1 WHERE NOTIFICATION_ID = ? AND USER_ID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $notification_id, $user_id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Database update failed']);
        }
        $stmt->close();
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Notification ID not provided']);
    }
}

$conn->close();
?>