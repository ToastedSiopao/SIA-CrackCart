<?php
header('Content-Type: application/json');
include '../../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'DELETE' || isset($_GET['id'])) {
    $product_id = intval($_GET['id'] ?? 0);

    if ($product_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid product ID.']);
        exit;
    }

    // Before deleting, check if the product is part of any order
    $check_stmt = $conn->prepare("SELECT COUNT(*) FROM product_order_items WHERE producer_id = ?"); // Corrected to use producer_id, assuming that's the FK
    $check_stmt->bind_param("i", $product_id);
    $check_stmt->execute();
    $check_stmt->bind_result($count);
    $check_stmt->fetch();
    $check_stmt->close();

    if ($count > 0) {
        // Instead of deleting, we can mark the product as 'inactive'
        $update_stmt = $conn->prepare("UPDATE PRICE SET STATUS = 'inactive' WHERE PRICE_ID = ?");
        $update_stmt->bind_param("i", $product_id);
        if ($update_stmt->execute()) {
             echo json_encode(['status' => 'success', 'message' => 'Product is in use and has been marked as inactive.']);
        } else {
             echo json_encode(['status' => 'error', 'message' => 'Could not mark product as inactive.']);
        }
        $update_stmt->close();
    } else {
        // No orders associated, so we can delete it
        $delete_stmt = $conn->prepare("DELETE FROM PRICE WHERE PRICE_ID = ?");
        $delete_stmt->bind_param("i", $product_id);
        if ($delete_stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Product deleted successfully!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Database error: Could not delete product.']);
        }
        $delete_stmt->close();
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

$conn->close();
?>