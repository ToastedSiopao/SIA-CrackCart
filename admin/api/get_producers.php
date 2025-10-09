<?php
header('Content-Type: application/json');
include '../../db_connect.php';

$query = "SELECT PRODUCER_ID, NAME FROM PRODUCER ORDER BY NAME ASC";
$result = $conn->query($query);

if ($result) {
    $producers = [];
    while ($row = $result->fetch_assoc()) {
        $producers[] = $row;
    }
    echo json_encode(['status' => 'success', 'data' => $producers]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Could not fetch producers.']);
}

$conn->close();
?>