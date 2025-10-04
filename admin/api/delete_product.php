<?php
header('Content-Type: application/json');
include('../../db_connect.php');
include('../../error_handler.php');

session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Access denied.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit();
}

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid product ID.']);
    exit();
}

if ($conn) {
    try {
        // We should also consider deleting related records in other tables if necessary (e.g., product_order_items)
        // For now, we'll just delete from the PRICE table.
        $stmt = $conn->prepare("DELETE FROM PRICE WHERE PRICE_ID = ?");
        $stmt->bind_param("i", $product_id);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode(['status' => 'success', 'message' => 'Product deleted successfully.']);
            } else {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Product not found.']);
            }
        } else {
            throw new Exception('Failed to delete the product.');
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    $stmt->close();
    $conn->close();
} else {
    http_response_code(503);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
}
?>