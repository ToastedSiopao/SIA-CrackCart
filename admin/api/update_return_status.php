<?php
session_start();
header('Content-Type: application/json');

// Security check: Ensure the user is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Authentication required.']);
    exit();
}

// Adjust path for the new location
include '../../db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['return_id']) || !isset($data['status'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    exit();
}

$return_id = (int)$data['return_id'];
$new_status = $data['status'];
$allowed_statuses = ['approved', 'rejected'];

if (!in_array($new_status, $allowed_statuses)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid status value.']);
    exit();
}

$conn->begin_transaction();

try {
    // Step 1: Update the status of the return request
    $stmt1 = $conn->prepare("UPDATE returns SET status = ? WHERE return_id = ?");
    if (!$stmt1) throw new Exception("Prepare statement failed: " . $conn->error);
    $stmt1->bind_param('si', $new_status, $return_id);
    if (!$stmt1->execute()) throw new Exception("Execute failed: " . $stmt1->error);
    $stmt1->close();

    // Step 2: If approved, update the related order's status to 'Refunded'
    if ($new_status === 'approved') {
        // First, get the order_id from the return_id
        $stmt2 = $conn->prepare("SELECT order_id FROM returns WHERE return_id = ?");
        if (!$stmt2) throw new Exception("Prepare statement failed: " . $conn->error);
        $stmt2->bind_param('i', $return_id);
        if (!$stmt2->execute()) throw new Exception("Execute failed: " . $stmt2->error);
        
        $result = $stmt2->get_result();
        if ($result->num_rows === 0) throw new Exception("Return ID not found.");
        $order_id = $result->fetch_assoc()['order_id'];
        $stmt2->close();

        // Now, update the product_orders table
        $stmt3 = $conn->prepare("UPDATE product_orders SET status = 'Refunded' WHERE order_id = ?");
        if (!$stmt3) throw new Exception("Prepare statement failed: " . $conn->error);
        $stmt3->bind_param('i', $order_id);
        if (!$stmt3->execute()) throw new Exception("Execute failed: " . $stmt3->error);
        $stmt3->close();
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Return status updated successfully.']);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    // Return a generic error to the user, but log the specific error for debugging
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An internal server error occurred. Please try again later.']);
}

$conn->close();
?>
