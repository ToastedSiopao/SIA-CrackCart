<?php
session_start();
header('Content-Type: application/json');

function send_error($code, $message) {
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $message]);
    exit();
}

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    send_error(403, 'Authentication required.');
}

$host = "sql101.infinityfree.com";
$user = "if0_39829885";
$pass = "alingremy108";
$db   = "if0_39829885_db";

$conn = @mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    error_log("API DB Connection Failed (update_return_status.php): " . mysqli_connect_error());
    send_error(500, "Internal server error: Could not connect to the database.");
}

$conn->set_charset("utf8mb4");

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['return_id']) || !isset($data['status'])) {
    send_error(400, 'Invalid input: return_id and status are required.');
}

$return_id = (int)$data['return_id'];
$new_status = $data['status'];
$allowed_statuses = ['approved', 'rejected'];

if (!in_array($new_status, $allowed_statuses)) {
    send_error(400, 'Invalid status value.');
}

$conn->begin_transaction();

try {
    $stmt1 = $conn->prepare("UPDATE returns SET status = ? WHERE return_id = ?");
    if (!$stmt1) throw new Exception("Prepare statement failed (stmt1): " . $conn->error);
    $stmt1->bind_param('si', $new_status, $return_id);
    if (!$stmt1->execute()) throw new Exception("Execute failed (stmt1): " . $stmt1->error);
    if ($stmt1->affected_rows === 0) throw new Exception("Return ID not found or status already set.");
    $stmt1->close();

    $stmt_info = $conn->prepare("SELECT order_id, user_id FROM returns WHERE return_id = ?");
    if (!$stmt_info) throw new Exception("Prepare statement failed (stmt_info): " . $conn->error);
    $stmt_info->bind_param('i', $return_id);
    if (!$stmt_info->execute()) throw new Exception("Execute failed (stmt_info): " . $stmt_info->error);
    
    $result = $stmt_info->get_result();
    if ($result->num_rows === 0) throw new Exception("Return ID not found when fetching details.");
    $return_data = $result->fetch_assoc();
    $order_id = $return_data['order_id'];
    $user_id = $return_data['user_id'];
    $stmt_info->close();

    $message = "Your return request for order #{$order_id} has been {$new_status}.";
    $link = "view_order.php?order_id={$order_id}";
    $stmt_notify = $conn->prepare("INSERT INTO NOTIFICATION (USER_ID, MESSAGE, link) VALUES (?, ?, ?)");
    if (!$stmt_notify) throw new Exception("Prepare statement failed (stmt_notify): " . $conn->error);
    $stmt_notify->bind_param('iss', $user_id, $message, $link);
    if (!$stmt_notify->execute()) throw new Exception("Execute failed (stmt_notify): " . $stmt_notify->error);
    $stmt_notify->close();

    if ($new_status === 'approved') {
        $stmt3 = $conn->prepare("UPDATE product_orders SET status = 'Refunded' WHERE order_id = ?");
        if (!$stmt3) throw new Exception("Prepare statement failed (stmt3): " . $conn->error);
        $stmt3->bind_param('i', $order_id);
        if (!$stmt3->execute()) throw new Exception("Execute failed (stmt3): " . $stmt3->error);
        $stmt3->close();
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Return status updated and user notified successfully.']);

} catch (Exception $e) {
    $conn->rollback();
    error_log("Update Return Status Error: " . $e->getMessage());
    send_error(500, 'An internal server error occurred. Please check the logs.');
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>