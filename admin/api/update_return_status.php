<?php
header('Content-Type: application/json');
include '../../db_connect.php';
include '../../log_function.php'; 
include '../../notification_function.php';

session_start();

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
    $coupon_generated_message = '';

    try {
        // Fetch all necessary info in one query, including the value of the returned item
        $stmt_info = $conn->prepare("\n            SELECT \n                po.user_id, \n                r.order_id, \n                r.reason, \n                (poi.quantity * poi.price_per_item) AS item_value\n            FROM returns r \n            JOIN product_orders po ON r.order_id = po.order_id \n            JOIN product_order_items poi ON r.order_item_id = poi.order_item_id\n            WHERE r.return_id = ?\n        ");
        $stmt_info->bind_param("i", $return_id);
        $stmt_info->execute();
        $result_info = $stmt_info->get_result();
        if (!($info = $result_info->fetch_assoc())) {
            throw new Exception("Return information could not be found.");
        }
        $user_id = $info['user_id'];
        $order_id = $info['order_id'];
        $reason = $info['reason'];
        $discount_value = (float)$info['item_value']; // Dynamic discount value
        $stmt_info->close();

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

        $notification_message = "Your return request for order #{$order_id} has been updated to '{$new_status}'.";

        // --- UPDATED: DYNAMIC COUPON GENERATION LOGIC ---
        $eligible_reasons = ['Damaged in transit', 'Item is expired'];
        if ($new_status === 'approved' && in_array($reason, $eligible_reasons) && $discount_value > 0) {
            $coupon_code = 'RETURN-' . strtoupper(uniqid());
            $expiry_date = date('Y-m-d', strtotime('+30 days'));

            $stmt_coupon = $conn->prepare("INSERT INTO coupons (coupon_code, user_id, discount_value, expiry_date) VALUES (?, ?, ?, ?)");
            $stmt_coupon->bind_param("sids", $coupon_code, $user_id, $discount_value, $expiry_date);
            if (!$stmt_coupon->execute()) {
                throw new Exception("Failed to generate coupon.");
            }
            $stmt_coupon->close();

            log_action('Coupon Generation', "Admin ID {$admin_id} issued coupon {$coupon_code} (Value: {$discount_value}) to user #{$user_id} for return #{$return_id}.");
            
            $formatted_discount = number_format($discount_value, 2);
            $coupon_generated_message = " A coupon worth ₱{$formatted_discount} has been issued to the user.";
            $notification_message = "Your return for order #{$order_id} was approved. We have issued a coupon worth ₱{$formatted_discount} to your account for the inconvenience.";
        }
        
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