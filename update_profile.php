<?php
session_start();
include("db_connect.php");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

$first_name = $_POST['first_name'] ?? '';
$last_name = $_POST['last_name'] ?? '';

$sql = "UPDATE USER SET FIRST_NAME = ?, LAST_NAME = ? WHERE USER_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssi", $first_name, $last_name, $user_id);

if ($stmt->execute()) {
    $_SESSION['user_name'] = $first_name;
    echo json_encode(['status' => 'success', 'message' => 'Profile updated successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update profile']);
}

if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);
    if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
        $sql = "UPDATE USER SET profile_picture = ? WHERE USER_ID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $target_file, $user_id);
        $stmt->execute();
    }
}
?>
