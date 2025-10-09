<?php
require_once 'session_handler.php';
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Mark a specific notification as read if requested
if (isset($_GET['mark_as_read'])) {
    $notification_id = $_GET['mark_as_read'];
    if (filter_var($notification_id, FILTER_VALIDATE_INT)) {
        $sql_update = "UPDATE NOTIFICATION SET IS_READ = 1 WHERE NOTIFICATION_ID = ? AND USER_ID = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("ii", $notification_id, $user_id);
        $stmt_update->execute();
        // We can exit here as this is a specific action
        echo json_encode(['success' => true, 'message' => 'Notification marked as read.']);
        exit;
    }
}

header('Content-Type: application/json');

// Fetch all notifications (read and unread, but we only show unread count)
$query_notif = "SELECT NOTIFICATION_ID, MESSAGE, IS_READ, CREATED_AT FROM NOTIFICATION WHERE USER_ID = ? ORDER BY CREATED_AT DESC LIMIT 10";
$stmt_notif = $conn->prepare($query_notif);
$stmt_notif->bind_param("i", $user_id);
$stmt_notif->execute();
$result_notif = $stmt_notif->get_result();
$notifications = $result_notif->fetch_all(MYSQLI_ASSOC);
$stmt_notif->close();

// Loop through notifications to generate a context-specific link
foreach ($notifications as &$notification) {
    $link = '#'; // Default link
    $message = $notification['MESSAGE'];

    // Check for incident reports
    if (preg_match('/incident has been reported for your order #(\d+)/', $message, $matches)) {
        $order_id = $matches[1];
        $link = "view_order.php?order_id={$order_id}";
    }
    // Check for return requests (add more rules as needed)
    elseif (preg_match('/return request for order item #(\d+)/', $message, $matches)) {
        // Future enhancement: Link to a specific return status page
        $link = 'my_orders.php';
    }

    $notification['link'] = $link;
}

// Fetch the count of unread notifications separately
$query_count = "SELECT COUNT(*) as unread_count FROM NOTIFICATION WHERE USER_ID = ? AND IS_READ = 0";
$stmt_count = $conn->prepare($query_count);
$stmt_count->bind_param("i", $user_id);
$stmt_count->execute();
$result_count = $stmt_count->get_result();
$unread_count = $result_count->fetch_assoc()['unread_count'];
$stmt_count->close();

$conn->close();

// Return the full structure
echo json_encode(['notifications' => $notifications, 'unread_count' => $unread_count]);
?>
