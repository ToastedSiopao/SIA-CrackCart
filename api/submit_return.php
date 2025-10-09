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

// --- SECURITY FIX: File Upload Validation ---
if ($reason === 'Damaged in transit') {
    if (isset($_FILES['damaged_image']) && $_FILES['damaged_image']['error'] == 0) {
        $file = $_FILES['damaged_image'];
        $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif'];
        $max_file_size = 5 * 1024 * 1024; // 5 MB

        // 1. Check file size
        if ($file['size'] > $max_file_size) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'File is too large. Maximum size is 5 MB.']);
            exit;
        }

        // 2. Check MIME type
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime_type = $finfo->file($file['tmp_name']);
        if (!in_array($mime_type, $allowed_mimes)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid file type. Only JPG, PNG, and GIF are allowed.']);
            exit;
        }

        // 3. Secure filename generation
        $upload_dir = '../uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true); // Use more secure permissions
        }
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $file_name = bin2hex(random_bytes(16)) . '.' . $file_extension;
        $target_file = $upload_dir . $file_name;
        
        // 4. Move the uploaded file
        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            $image_path = 'uploads/' . $file_name;
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to store the uploaded image.']);
            exit;
        }
    } else {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'An image is required for returns due to damage.']);
        exit;
    }
}
// --- END SECURITY FIX ---

$conn->begin_transaction();

try {
    $verify_stmt = $conn->prepare(
        "SELECT po.order_id, poi.product_type, poi.producer_id, poi.tray_size
         FROM product_order_items poi 
         JOIN product_orders po ON poi.order_id = po.order_id 
         WHERE poi.order_item_id = ? AND po.user_id = ? AND po.status = 'delivered'"
    );
    $verify_stmt->bind_param("ii", $order_item_id, $user_id);
    $verify_stmt->execute();
    $result = $verify_stmt->get_result();
    if ($result->num_rows == 0) {
        throw new Exception('This item is not eligible for return. It might not belong to you, the order may not have been delivered, or a return is already in process.');
    }
    $item_data = $result->fetch_assoc();
    $order_id = $item_data['order_id'];
    $product_type = $item_data['product_type'];
    $producer_id = $item_data['producer_id'];
    $tray_size = $item_data['tray_size']; // Get tray size
    $verify_stmt->close();

    // Match product using tray size as well
    $product_stmt = $conn->prepare("SELECT PRICE_ID FROM PRICE WHERE TYPE = ? AND PRODUCER_ID = ? AND TRAY_SIZE = ?");
    $product_stmt->bind_param("sii", $product_type, $producer_id, $tray_size);
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

    $insert_stmt = $conn->prepare("INSERT INTO returns (order_id, order_item_id, user_id, product_id, reason, image_path, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
    $insert_stmt->bind_param("iiiiss", $order_id, $order_item_id, $user_id, $product_id, $reason, $image_path);
    
    if (!$insert_stmt->execute()) {
        throw new Exception('Failed to save your return request.');
    }
    $insert_stmt->close();

    log_action($user_id, 'Return Requested', "User requested return for order item ID: {$order_item_id}");

    $conn->commit();

    echo json_encode(['status' => 'success', 'message' => 'Your return request has been submitted successfully!']);

} catch (Exception $e) {
    $conn->rollback();
    // Cleanup uploaded file on error
    if ($image_path && file_exists('../' . $image_path)) {
        unlink('../' . $image_path);
    }
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
?>