<?php
require_once 'error_handler.php';

header('Content-Type: application/json');

include("db_connect.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullName = mysqli_real_escape_string($conn, $_POST['fullName']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

    if ($password !== $confirmPassword) {
        echo json_encode(['error' => ['message' => 'Passwords do not match']]);
        exit();
    }

    if (strlen($password) < 8) {
        echo json_encode(['error' => ['message' => 'Password must be at least 8 characters long']]);
        exit();
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $checkEmail = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($conn, $checkEmail);

    if (mysqli_num_rows($result) > 0) {
        echo json_encode(['error' => ['message' => 'Email already registered. Please login.']]);
        exit();
    }

    $sql = "INSERT INTO users (full_name, email, phone, password, role, status, created_at) 
            VALUES ('$fullName', '$email', '$phone', '$hashedPassword', 'customer', 'active', NOW())";

    if (mysqli_query($conn, $sql)) {
        echo json_encode(['success' => true]);
        exit();
    } else {
        echo json_encode(['error' => ['message' => 'Error creating account: ' . mysqli_error($conn)]]);
        exit();
    }
}
?>