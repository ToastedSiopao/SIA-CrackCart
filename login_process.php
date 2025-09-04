<?php
require_once 'error_handler.php';

header('Content-Type: application/json');

session_start();
include("db_connect.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email='$email' AND status='active'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];

            echo json_encode(['success' => true]);
            exit();
        } else {
            echo json_encode(['error' => ['message' => 'Invalid email or password']]);
            exit();
        }
    } else {
        echo json_encode(['error' => ['message' => 'Invalid email or password']]);
        exit();
    }
}
?>