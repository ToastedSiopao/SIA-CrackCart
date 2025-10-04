<?php
header('Content-Type: application/json');
include('../../db_connect.php');

session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Access denied.']);
    exit();
}

if ($conn) {
    try {
        $query = "SELECT PRODUCER_ID, NAME FROM PRODUCER ORDER BY NAME ASC";
        $result = $conn->query($query);
        $producers = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $producers]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to fetch producers.']);
    }
    $conn->close();
} else {
    http_response_code(503);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
}
?>