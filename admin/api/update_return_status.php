<?php
header('Content-Type: application/json');
include '../../db_connect.php';
include '../../log_function.php'; 
include '../../notification_function.php';

session_start();
// Security check: ensure the user is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Access denied.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$return_id = $data['return_id'] ?? 0;
$new_status = $data['status'] ?? '';
$admin_id = $_SESSION['user_id'];

if ($return_id > 0 && !empty($new_status)) {
    $conn->begin_transaction();
    $coupon_generated_message = ''; // For admin response

    try {
        // Fetch user_id, order_id, AND reason for the return
        $stmt_info = $conn->prepare("SELECT po.user_id, r.order_id, r.reason FROM returns r JOIN product_orders po ON r.order_id = po.order_id WHERE r.return_id = ?");
        $stmt_info->bind_param("i", $return_id);
        $stmt_info->execute();
        $result_info = $stmt_info->get_result();
        if (!($info = $result_info->fetch_assoc())) {
            throw new Exception("Return info not found.");
        }
        $user_id = $info['user_id'];
        $order_id = $info['order_id'];
        $reason = $info['reason'];
        $stmt_info->close();

        $notification_message = "";// For user notification

        // Update return status
        $update_sql = "UPDATE returns SET status = ?";
        if ($new_status === 'approved') {
            $update_sql .= ", approved_at = NOW()";
        }
        $update_sql .= " WHERE return_id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param('si', $new_status, $return_id);

        if (!$stmt->execute()) {
            throw new Exception('Failed to update return status.');
        }

        if ($new_status === 'approved') {
            // --- NEW: COUPON GENERATION LOGIC ---
            if ($reason === 'Cracked Eggs') {
                $coupon_code = 'CRACKED-' . strtoupper(uniqid());
                $discount_value = 50.00; // Fixed ₱50 discount

                $stmt_coupon = $conn->prepare("INSERT INTO coupons (coupon_code, user_id, discount_value) VALUES (?, ?, ?)");
                $stmt_coupon->bind_param("sid", $coupon_code, $user_id, $discount_value);
                if (!$stmt_coupon->execute()) {
                    throw new Exception("Failed to generate coupon.");
                }
                $stmt_coupon->close();

                log_action('Coupon Generation', "Admin ID {$admin_id} issued coupon {$coupon_code} to user #{$user_id} for return #{$return_id}.");
                $coupon_generated_message = " A ₱50.00 coupon has been issued to the user.";
                $notification_message = "Your return for order #{$order_id} was approved. We have issued a ₱50.00 coupon to your account for the inconvenience.";

            } else {
                $notification_message = "Your return request for order #{$order_id} has been updated to 'approved'.";
            }
        } else {
            $notification_message = "Your return request for order #{$order_id} has been updated to '{$new_status}'.";
        }
        
        // Create a notification for the customer
        create_notification($conn, $user_id, $notification_message, "profilePage.php#orders");

        $conn->commit();
        echo json_encode(['status' => 'success', 'message' => 'Return status updated successfully.' . $coupon_generated_message]);

    } catch (Exception $e) {
        $conn->rollback();
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }

    if (isset($stmt)) $stmt->close();
    $conn->close();
} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid data provided.']);
}
?>
