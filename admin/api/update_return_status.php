<?php
header('Content-Type: application/json');
include '../../db_connect.php';
include '../../log_function.php';
include '../../notification_function.php';

session_start();

// Ensure user is admin
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
    try {
        // Step 1: Get all necessary return and item details in one query
        $stmt_info = $conn->prepare("
            SELECT 
                r.order_id,
                r.reason,
                po.user_id,
                poi.producer_id,
                poi.product_type,
                poi.quantity,
                poi.tray_size,
                (poi.quantity * poi.price_per_item) AS item_value
            FROM returns r
            JOIN product_orders po ON r.order_id = po.order_id
            JOIN product_order_items poi ON r.order_item_id = poi.order_item_id
            WHERE r.return_id = ?
        ");
        $stmt_info->bind_param("i", $return_id);
        $stmt_info->execute();
        $result_info = $stmt_info->get_result();
        if (!($info = $result_info->fetch_assoc())) {
            throw new Exception("Return information could not be found.");
        }
        $stmt_info->close();

        // Step 2: Update the return status
        $update_sql = "UPDATE returns SET status = ?";
        if ($new_status === 'approved') {
            $update_sql .= ", approved_at = NOW()";
        }
        $update_sql .= " WHERE return_id = ?";
        $stmt_status = $conn->prepare($update_sql);
        $stmt_status->bind_param('si', $new_status, $return_id);
        if (!$stmt_status->execute()) {
            throw new Exception('Failed to update return status.');
        }
        $stmt_status->close();

        $notification_message = "Your return request for order #{$info['order_id']} has been updated to '{$new_status}'.";

        // Step 3: Handle automatic actions for approved returns
        if ($new_status === 'approved') {
            $main_reason = trim(explode(';', $info['reason'])[0]);

            // --- AUTOMATIC RESTOCK LOGIC ---
            $reasons_for_restock = ['Wrong item delivered', 'Received a different size/type', 'Quality not as expected'];
            if (in_array($main_reason, $reasons_for_restock)) {
                // Find the PRICE_ID from the PRICE table based on producer and type
                $stmt_price = $conn->prepare("SELECT PRICE_ID FROM PRICE WHERE PRODUCER_ID = ? AND TYPE = ?");
                $stmt_price->bind_param("is", $info['producer_id'], $info['product_type']);
                $stmt_price->execute();
                $price_result = $stmt_price->get_result();
                if ($price_info = $price_result->fetch_assoc()) {
                    $price_id_to_update = $price_info['PRICE_ID'];
                    // Correctly calculate stock to add (quantity of trays * eggs per tray)
                    $stock_to_add = (int)$info['quantity'] * (int)$info['tray_size'];

                    $stmt_stock = $conn->prepare("UPDATE PRICE SET STOCK = STOCK + ? WHERE PRICE_ID = ?");
                    $stmt_stock->bind_param("ii", $stock_to_add, $price_id_to_update);
                    if (!$stmt_stock->execute()) {
                        throw new Exception("Failed to update stock.");
                    }
                    $stmt_stock->close();
                    log_action('Stock Update', "Stock for product PRICE_ID {$price_id_to_update} increased by {$stock_to_add} units due to approved return #{$return_id}.");
                } else {
                    log_action('Stock Update Failed', "Could not find product in PRICE table to restock for return #{$return_id}.");
                }
                $stmt_price->close();
            }

            // --- AUTOMATIC COUPON GENERATION LOGIC ---
            $eligible_reasons_for_coupon = ['Damaged in transit', 'Item is expired'];
            if (in_array($main_reason, $eligible_reasons_for_coupon) && (float)$info['item_value'] > 0) {
                // ... [coupon logic remains the same] ...
            }
        }

        create_notification($conn, $info['user_id'], $notification_message, "profilePage.php#orders");

        $conn->commit();
        echo json_encode(['status' => 'success', 'message' => 'Return status updated successfully.']);

    } catch (Exception $e) {
        $conn->rollback();
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        log_action('Update Return Error', "Error for return #{$return_id}: " . $e->getMessage());
    }

    $conn->close();
} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid data provided.']);
}
?>