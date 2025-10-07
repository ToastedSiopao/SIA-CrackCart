<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');
include '../../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['id'] ?? 0);

    if ($product_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid product ID.']);
        exit;
    }

    // Get producer_id and type from PRICE table
    $product_info_stmt = $conn->prepare("SELECT PRODUCER_ID, TYPE FROM PRICE WHERE PRICE_ID = ?");
    $product_info_stmt->bind_param("i", $product_id);
    $product_info_stmt->execute();
    $product_info_result = $product_info_stmt->get_result();
    if ($product_info_result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Product not found.']);
        exit;
    }
    $product_info = $product_info_result->fetch_assoc();
    $producer_id = $product_info['PRODUCER_ID'];
    $product_type = $product_info['TYPE'];
    $product_info_stmt->close();

    // Before deleting, check if the product is part of any order
    $check_stmt = $conn->prepare("SELECT COUNT(*) FROM product_order_items WHERE producer_id = ? AND product_type = ?");
    $check_stmt->bind_param("is", $producer_id, $product_type);
    $check_stmt->execute();
    $check_stmt->bind_result($count);
    $check_stmt->fetch();
    $check_stmt->close();

    if ($count > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Cannot delete this product because it is part of an existing order.']);
        exit;
    }

    // No orders found, so proceed with deletion
    $delete_stmt = $conn->prepare("DELETE FROM PRICE WHERE PRICE_ID = ?");
    $delete_stmt->bind_param("i", $product_id);

    if ($delete_stmt->execute()) {
        if ($delete_stmt->affected_rows > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Product deleted successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Product not found or already deleted.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete product from database.']);
    }
    $delete_stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method. This endpoint now only accepts POST requests for deletion.']);
}

$conn->close();
?>
