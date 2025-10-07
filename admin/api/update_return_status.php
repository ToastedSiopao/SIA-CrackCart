<?php
session_start();
header('Content-Type: application/json');

// Centralized error response function
function send_error($code, $message) {
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $message]);
    exit();
}

// Security check: Ensure the user is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    send_error(403, 'Authentication required.');
}

// --- Direct, Robust Database Connection for API Endpoint ---
$host = "sql101.infinityfree.com";
$user = "if0_39829885";
$pass = "alingremy108";
$db   = "if0_39829885_db";

// Use @ to suppress the default warning, as we will handle the error manually.
$conn = @mysqli_connect($host, $user, $pass, $db);

// If the connection fails, send a proper JSON error instead of dying.
if (!$conn) {
    // Log the actual error for debugging purposes.
    error_log("API DB Connection Failed (update_return_status.php): " . mysqli_connect_error());
    // Send a clean JSON response to the front end.
    send_error(500, "Internal server error: Could not connect to the database.");
}

// Set character set for the connection
$conn->set_charset("utf8mb4");
// --- End of Connection ---


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
    // Step 1: Update the status of the return request
    $stmt1 = $conn->prepare("UPDATE returns SET status = ? WHERE return_id = ?");
    if (!$stmt1) throw new Exception("Prepare statement failed (stmt1): " . $conn->error);
    $stmt1->bind_param('si', $new_status, $return_id);
    if (!$stmt1->execute()) throw new Exception("Execute failed (stmt1): " . $stmt1->error);
    $stmt1->close();

    // Step 2: If approved, update the related order's status to 'Refunded'
    if ($new_status === 'approved') {
        // Get the order_id from the return_id
        $stmt2 = $conn->prepare("SELECT order_id FROM returns WHERE return_id = ?");
        if (!$stmt2) throw new Exception("Prepare statement failed (stmt2): " . $conn->error);
        $stmt2->bind_param('i', $return_id);
        if (!$stmt2->execute()) throw new Exception("Execute failed (stmt2): " . $stmt2->error);
        
        $result = $stmt2->get_result();
        if ($result->num_rows === 0) throw new Exception("Return ID not found.");
        $order_data = $result->fetch_assoc();
        $order_id = $order_data['order_id'];
        $stmt2->close();

        // Now, update the product_orders table
        $stmt3 = $conn->prepare("UPDATE product_orders SET status = 'Refunded' WHERE order_id = ?");
        if (!$stmt3) throw new Exception("Prepare statement failed (stmt3): " . $conn->error);
        $stmt3->bind_param('i', $order_id);
        if (!$stmt3->execute()) throw new Exception("Execute failed (stmt3): " . $stmt3->error);
        $stmt3->close();
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Return status updated successfully.']);

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
