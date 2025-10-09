<?php
header('Content-Type: application/json');
include '../../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $producer_id = intval($_POST['producer_id'] ?? 0);
    $type = trim($_POST['type'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $per = trim($_POST['per'] ?? 'tray');
    $stock = intval($_POST['stock'] ?? 0);
    $tray_size = intval($_POST['tray_size'] ?? 30);
    $status = 'active'; // Default status

    if ($producer_id <= 0 || empty($type) || $price <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid input. Please check all fields.']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO PRICE (PRODUCER_ID, TYPE, PRICE, PER, STATUS, STOCK, TRAY_SIZE) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isdssii", $producer_id, $type, $price, $per, $status, $stock, $tray_size);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Product added successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

$conn->close();
?>
