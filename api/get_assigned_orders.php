<?php
require_once __DIR__ . '../../session_handler.php';
require_once __DIR__ . '../../db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'driver') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

$driver_id = $_SESSION['user_id'];

$query = "SELECT 
            po.order_id, 
            CONCAT(u.FIRST_NAME, ' ', u.LAST_NAME) as user_name, 
            CONCAT(a.address_line1, ', ', a.city, ', ', a.state, ' ', a.zip_code) as shipping_address,
            po.total_amount, 
            po.status
          FROM product_orders po
          JOIN USER u ON po.user_id = u.USER_ID
          JOIN user_addresses a ON po.shipping_address_id = a.address_id
          WHERE po.vehicle_id IN (SELECT vehicle_id FROM Vehicle WHERE driver_id = ?)
          AND po.status = 'shipped'
          ORDER BY po.order_date DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $driver_id);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}

echo json_encode(['success' => true, 'data' => $orders]);

$conn->close();
?>