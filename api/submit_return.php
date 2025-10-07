<?php
session_start();
include '../db_connect.php';
include '../log_function.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Authentication required. Please log in.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$order_item_id = isset($_POST['order_item_id']) ? intval($_POST['order_item_id']) : 0;
$reason = trim($_POST['reason'] ?? '');
$image_path = null;

if ($order_item_id === 0 || empty($reason)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Please provide an item and a reason for the return.']);
    exit;
}

if ($reason === 'Damaged in transit') {
    if (isset($_FILES['damaged_image']) && $_FILES['damaged_image']['error'] == 0) {
        $upload_dir = '../uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_name = uniqid('return_', true) . '_' . basename($_FILES['damaged_image']['name']);
        $target_file = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['damaged_image']['tmp_name'], $target_file)) {
            $image_path = 'uploads/' . $file_name;
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to upload the image.']);
            exit;
        }
    } else {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'An image is required for returns due to damage.']);
        exit;
    }
}

$conn->begin_transaction();

try {
    $verify_stmt = $conn->prepare(
        "SELECT po.order_id, poi.product_type, poi.producer_id
         FROM product_order_items poi 
         JOIN product_orders po ON poi.order_id = po.order_id 
         WHERE poi.order_item_id = ? AND po.user_id = ? AND po.status = 'delivered'"
    );
    $verify_stmt->bind_param("ii", $order_item_id, $user_id);
    $verify_stmt->execute();
    $result = $verify_stmt->get_result();
    if ($result->num_rows == 0) {
        throw new Exception('This item is not eligible for return. It might not belong to you or the order has not been delivered yet.');
    }
    $item_data = $result->fetch_assoc();
    $order_id = $item_data['order_id'];
    $product_type = $item_data['product_type'];
    $producer_id = $item_data['producer_id'];
    $verify_stmt->close();

    $product_stmt = $conn->prepare("SELECT PRICE_ID FROM PRICE WHERE TYPE = ? AND PRODUCER_ID = ?");
    $product_stmt->bind_param("si", $product_type, $producer_id);
    $product_stmt->execute();
    $product_result = $product_stmt->get_result();
    if($product_result->num_rows === 0) {
        throw new Exception("Could not find a matching product ID to process the return.");
    }
    $product_id = $product_result->fetch_assoc()['PRICE_ID'];
    $product_stmt->close();

    $check_stmt = $conn->prepare("SELECT return_id FROM returns WHERE order_item_id = ?");
    $check_stmt->bind_param("i", $order_item_id);
    $check_stmt->execute();
    if ($check_stmt->get_result()->num_rows > 0) {
        throw new Exception('A return request has already been submitted for this item.');
    }
    $check_stmt->close();

    $insert_stmt = $conn->prepare("INSERT INTO returns (order_id, order_item_id, user_id, product_id, reason, image_path) VALUES (?, ?, ?, ?, ?, ?)");
    $insert_stmt->bind_param("iiiiss", $order_id, $order_item_id, $user_id, $product_id, $reason, $image_path);
    
    if (!$insert_stmt->execute()) {
        if ($image_path && file_exists('../' . $image_path)) {
            unlink('../' . $image_path);
        }
        throw new Exception('Failed to save your return request.');
    }
    $insert_stmt->close();

    log_action($user_id, 'Return Requested', "User requested return for order_item_id: {$order_item_id}");

    $conn->commit();

    echo json_encode(['status' => 'success', 'message' => 'Your return request has been submitted successfully!']);

} catch (Exception $e) {
    $conn->rollback();
    if ($image_path && file_exists('../' . $image_path)) {
        unlink('../' . $image_path);
    }
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
?>
