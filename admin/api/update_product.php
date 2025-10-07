<?php
header('Content-Type: application/json');
include '../../db_connect.php';
include '../../error_handler.php';

session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Access denied.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $price_id = isset($_POST['price_id']) ? (int)$_POST['price_id'] : 0;
    $type = $_POST['type'] ?? '';
    $price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
    $per = $_POST['per'] ?? 'tray';
    $stock = isset($_POST['stock']) ? (int)$_POST['stock'] : 0;
    $tray_size = isset($_POST['tray_size']) ? (int)$_POST['tray_size'] : 30;

    if ($price_id > 0 && !empty($type) && $price > 0 && $stock >= 0) {
        try {
            $stmt = $conn->prepare("UPDATE PRICE SET TYPE = ?, PRICE = ?, PER = ?, STOCK = ?, tray_size = ? WHERE PRICE_ID = ?");
            $stmt->bind_param("sdsiii", $type, $price, $per, $stock, $tray_size, $price_id);

            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    echo json_encode(['status' => 'success', 'message' => 'Product updated successfully.']);
                } else {
                    echo json_encode(['status' => 'info', 'message' => 'No changes were made to the product.']);
                }
            } else {
                throw new Exception("Failed to update product: " . $stmt->error);
            }
            $stmt->close();
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    } else {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid product data provided.']);
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

$conn->close();
?>
