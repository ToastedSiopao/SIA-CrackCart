<?php
include "../error_handler.php";
session_start();
header("Content-Type: application/json");
include("../db_connect.php");

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id === 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Order ID is required.']);
    exit();
}

// Fetch the main order details
$stmt = $conn->prepare("SELECT po.*, a.address_line1 AS street, a.city, a.state, a.zip_code, a.country FROM product_orders po JOIN user_addresses a ON po.shipping_address_id = a.address_id WHERE po.order_id = ? AND po.user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Order not found.']);
    exit();
}

// Fetch order items and join with returns to get return status for each specific item.
$stmt_items = $conn->prepare("\n    SELECT \n        poi.order_item_id, \n        poi.product_type, \n        poi.price_per_item, \n        poi.quantity, \n        poi.tray_size, \n        poi.is_reviewed, \n        r.status AS return_status \n    FROM \n        product_order_items poi\n    LEFT JOIN \n        returns r ON poi.order_item_id = r.order_item_id\n    WHERE \n        poi.order_id = ?\n");
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$items_result = $stmt_items->get_result();
$items = [];
while($item = $items_result->fetch_assoc()) {
    $item['is_reviewed'] = (bool)$item['is_reviewed']; // Cast to boolean for correct JSON type
    $items[] = $item;
}

$order['items'] = $items;

// Fetch from delivery_incidents
$stmt_incidents = $conn->prepare("SELECT incident_id, incident_type, description as issue_description, status, reported_at as created_at FROM delivery_incidents WHERE order_id = ? ORDER BY reported_at DESC");
$stmt_incidents->bind_param("i", $order_id);
$stmt_incidents->execute();
$incidents_result = $stmt_incidents->get_result();
$incidents = [];
while($incident = $incidents_result->fetch_assoc()) {
    $incidents[] = $incident;
}
$order['delivery_issues'] = $incidents;

// Check if there is an actionable incident
$has_actionable_incident = false;
foreach ($incidents as $incident) {
    if ($incident['status'] === 'reported') {
        $has_actionable_incident = true;
        break;
    }
}

// If there is an actionable incident, find the corresponding notification ID
if ($has_actionable_incident) {
    // The notification message is expected to contain 'incident' and 'order #'
    $message_pattern = "%incident%order #$order_id%";
    
    // Find the most recent notification for this incident, regardless of IS_READ status
    $stmt_notification = $conn->prepare(
        "SELECT NOTIFICATION_ID FROM NOTIFICATION WHERE USER_ID = ? AND MESSAGE LIKE ? ORDER BY CREATED_AT DESC LIMIT 1"
    );
    $stmt_notification->bind_param("is", $user_id, $message_pattern);
    $stmt_notification->execute();
    $notification_result = $stmt_notification->get_result();
    
    if ($notification_row = $notification_result->fetch_assoc()) {
        $order['notification_id'] = $notification_row['NOTIFICATION_ID'];
    }
}

echo json_encode(['status' => 'success', 'data' => $order]);

$conn->close();
?>
