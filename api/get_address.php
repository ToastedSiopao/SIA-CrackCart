<?php
require_once '../session_handler.php';
require_once '../db_connect.php';

if (isset($_GET['id'])) {
    $address_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT * FROM user_addresses WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $address_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $address = $result->fetch_assoc();
        echo json_encode($address);
    } else {
        echo json_encode(['error' => 'Address not found']);
    }
} else {
    echo json_encode(['error' => 'No address ID specified']);
}
?>
