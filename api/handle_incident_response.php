<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

include_once('../db_connect.php');

$data = json_decode(file_get_contents('php://input'), true);

$order_id = $data['order_id'] ?? null;
$decision = $data['decision'] ?? null; // 'cancel' or 'replace'
$notification_id = $data['notification_id'] ?? null;

if (!$order_id || !$decision || !$notification_id) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields.']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Begin transaction
$conn->begin_transaction();

try {
    // Verify that the user owns the order
    $verify_sql = "SELECT user_id FROM product_orders WHERE order_id = ? AND user_id = ?";
    $stmt = $conn->prepare($verify_sql);
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        throw new Exception('User does not own this order.');
    }
    $stmt->close();

    $new_order_status = '';
    $new_incident_status = '';

    if ($decision === 'cancel') {
        $new_order_status = 'Cancelled';
        $new_incident_status = 'User Responded - Cancelled';
    } else if ($decision === 'replace') {
        $new_order_status = 'Awaiting Replacement';
        $new_incident_status = 'User Responded - Replacement Requested';
    }

    // Update order status
    $update_order_sql = "UPDATE product_orders SET status = ? WHERE order_id = ?";
    $stmt = $conn->prepare($update_order_sql);
    $stmt->bind_param("si", $new_order_status, $order_id);
    $stmt->execute();
    $stmt->close();

    // Mark the user's notification as read
    $update_notification_sql = "UPDATE NOTIFICATION SET IS_READ = 1 WHERE NOTIFICATION_ID = ?";
    $stmt = $conn->prepare($update_notification_sql);
    $stmt->bind_param("i", $notification_id);
    $stmt->execute();
    $stmt->close();

    // Update the incident status to reflect user's decision, instead of resolving it.
    $update_incident_sql = "UPDATE delivery_incidents SET status = ? WHERE order_id = ? AND status = 'reported'";
    $stmt = $conn->prepare($update_incident_sql);
    $stmt->bind_param("si", $new_incident_status, $order_id);
    $stmt->execute();
    $stmt->close();

    // Notify admins of the user's decision
    $admin_sql = "SELECT USER_ID FROM USER WHERE ROLE = 'admin'";
    $admin_result = $conn->query($admin_sql);
    $admins = $admin_result->fetch_all(MYSQLI_ASSOC);
    $admin_result->close();

    $decision_text = ($decision === 'cancel') ? 'chosen to cancel' : 'requested a replacement for';
    $admin_message = "User #$user_id has responded to an incident. They have $decision_text order #$order_id.";

    foreach ($admins as $admin) {
        $admin_id = $admin['USER_ID'];
        $admin_notification_sql = "INSERT INTO NOTIFICATION (USER_ID, MESSAGE) VALUES (?, ?)";
        $stmt_admin = $conn->prepare($admin_notification_sql);
        $stmt_admin->bind_param("is", $admin_id, $admin_message);
        $stmt_admin->execute();
        $stmt_admin->close();
    }

    // Commit transaction
    $conn->commit();

    echo json_encode(['status' => 'success', 'message' => 'Your decision has been recorded and the admin has been notified.']);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
?>
