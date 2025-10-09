<?php
session_start();
header('Content-Type: application/json');
require_once '../db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$has_unresolved_incident = false;

// Find any orders belonging to the user that have an incident that is NOT resolved.
$sql = "SELECT di.incident_id
        FROM delivery_incidents di
        JOIN product_orders po ON di.order_id = po.order_id
        WHERE po.user_id = ? AND di.status != 'Resolved'
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $has_unresolved_incident = true;
}

$stmt->close();
$conn->close();

// The key in the JSON response must match what the frontend script expects.
// The scripts in sidebar.php and offcanvas_sidebar.php expect 'has_incident'.
echo json_encode(['status' => 'success', 'has_incident' => $has_unresolved_incident]);
?>