<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

include_once('db_connect.php');

$user_id = $_SESSION['user_id'];

// The NOTIFICATION table does not have an order_id or type column.
$sql = "SELECT NOTIFICATION_ID, MESSAGE, IS_READ, CREATED_AT FROM NOTIFICATION WHERE USER_ID = ? ORDER BY CREATED_AT DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notification_html = '<a href="#" class="list-group-item list-group-item-action' . ($row['IS_READ'] ? '' : ' list-group-item-secondary') . '" aria-current="true" data-notification-id="' . $row['NOTIFICATION_ID'] . '">';
    $notification_html .= '<div class="d-flex w-100 justify-content-between">';
    $notification_html .= '<h5 class="mb-1">' . htmlspecialchars($row['MESSAGE']) . '</h5>';
    $notification_html .= '<small>' . date('M j, Y, g:i a', strtotime($row['CREATED_AT'])) . '</small>';
    $notification_html .= '</div>';

    // Check if the message contains the incident text, and if it is unread.
    if (strpos($row['MESSAGE'], 'incident with your order') !== false && !$row['IS_READ']) {
        // Extract order_id from the message text.
        preg_match('/#(\d+)/', $row['MESSAGE'], $matches);
        $order_id = $matches[1] ?? null;

        if ($order_id) {
            $notification_html .= '<div class="mt-2">';
            $notification_html .= '<button class="btn btn-success btn-sm incident-decision-btn" data-decision="replace" data-order-id="' . $order_id . '" data-notification-id="' . $row['NOTIFICATION_ID'] . '">Request Replacement</button>';
            $notification_html .= ' <button class="btn btn-danger btn-sm incident-decision-btn" data-decision="cancel" data-order-id="' . $order_id . '" data-notification-id="' . $row['NOTIFICATION_ID'] . '">Cancel Order</button>';
            $notification_html .= '</div>';
        }
    }
    
    $notification_html .= '</a>';
    $notifications[] = $notification_html;
}

$unread_count_sql = "SELECT COUNT(*) as unread_count FROM NOTIFICATION WHERE USER_ID = ? AND IS_READ = 0";
$stmt_unread = $conn->prepare($unread_count_sql);
$stmt_unread->bind_param("i", $user_id);
$stmt_unread->execute();
$unread_result = $stmt_unread->get_result();
$unread_count = $unread_result->fetch_assoc()['unread_count'];

echo json_encode(['status' => 'success', 'notifications' => $notifications, 'unread_count' => $unread_count]);

$stmt->close();
$stmt_unread->close();
$conn->close();
?>
