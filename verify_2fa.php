<?php
require_once 'error_handler.php';

header('Content-Type: application/json');

session_start();
include("db_connect.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $two_fa_code = $_POST['2fa-code'];

    if (isset($_SESSION['2fa_code']) && $two_fa_code == $_SESSION['2fa_code']) {
        // 2FA code is correct. Log the user in.
        $user_id = $_SESSION['2fa_user_id'];
        $sql = "SELECT * FROM users WHERE id='$user_id'";
        $result = mysqli_query($conn, $sql);
        $user = mysqli_fetch_assoc($result);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];

        // Unset the 2FA session variables
        unset($_SESSION['2fa_code']);
        unset($_SESSION['2fa_user_id']);

        echo json_encode(['success' => true]);
        exit();
    } else {
        echo json_encode(['error' => ['message' => 'Invalid 2FA code']]);
        exit();
    }
}
?>