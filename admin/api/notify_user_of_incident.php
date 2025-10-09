<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

include_once('../../db_connect.php');

$data = json_decode(file_get_contents('php://input'), true);

$incident_id = $data['incident_id'] ?? null;
$order_id = $data['order_id'] ?? null;

if (!$incident_id || !$order_id) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields.']);
    exit();
}

// Begin transaction
$conn->begin_transaction();

try {
    // 1. Update incident status in delivery_incidents table
    $update_sql = "UPDATE delivery_incidents SET status = 'Pending User Action' WHERE incident_id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("i", $incident_id);
    $stmt->execute();
    $stmt->close();

    // 2. Get user_id from product_orders table
    $user_sql = "SELECT user_id FROM product_orders WHERE order_id = ?";
    $stmt = $conn->prepare($user_sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $user_id = $row['user_id'];
    } else {
        throw new Exception('Could not find user for this order.');
    }
    $stmt->close();

    // 3. Create notification for the user in the NOTIFICATION table
    $message = "There was an incident with your order #$order_id. Please review and decide on the next step.";
    // The NOTIFICATION table does not have a `type` or `order_id` column.
    $notification_sql = "INSERT INTO NOTIFICATION (USER_ID, MESSAGE) VALUES (?, ?)";
    $stmt = $conn->prepare($notification_sql);
    $stmt->bind_param("is", $user_id, $message);
    $stmt->execute();
    $stmt->close();

    // Commit transaction
    $conn->commit();

    echo json_encode(['status' => 'success', 'message' => 'User notified successfully. The incident is pending their action.']);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}

$conn->close();
?>
