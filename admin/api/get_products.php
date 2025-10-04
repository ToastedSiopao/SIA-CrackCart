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

if ($conn) {
    try {
        $query = "SELECT p.PRICE_ID, p.TYPE, p.PRICE, p.PER, pr.NAME as PRODUCER_NAME FROM PRICE p JOIN PRODUCER pr ON p.PRODUCER_ID = pr.PRODUCER_ID ORDER BY p.PRICE_ID DESC";
        $result = $conn->query($query);

        if ($result) {
            $products = $result->fetch_all(MYSQLI_ASSOC);
            echo json_encode(['status' => 'success', 'data' => $products]);
        } else {
            throw new Exception('Failed to fetch products.');
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    $conn->close();
} else {
    http_response_code(503);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
}
?>