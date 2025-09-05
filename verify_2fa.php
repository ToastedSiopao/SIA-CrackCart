<?php
require_once 'error_handler.php';

header('Content-Type: application/json');
session_start();
include("db_connect.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Must match the input name="code" in your 2fa.php form
    $two_fa_code = $_POST['code'] ?? '';

    if (isset($_SESSION['2fa_code']) && $two_fa_code === $_SESSION['2fa_code']) {
        // 2FA code is correct. Log the user in.
        $user_id = intval($_SESSION['2fa_user_id']); // sanitize

        $sql = "SELECT * FROM users WHERE user_id='$user_id' LIMIT 1"; // ✅ correct column
        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);

            // Save user session
            $_SESSION['user_id']    = $user['user_id'];
            $_SESSION['user_name']  = $user['full_name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role']  = $user['role'];

            // Clear 2FA temporary session data
            unset($_SESSION['2fa_code']);
            unset($_SESSION['2fa_user_id']);

            echo json_encode(['success' => true]);
            exit();
        } else {
            echo json_encode(['error' => ['message' => 'User not found']]);
            exit();
        }
    } else {
        echo json_encode(['error' => ['message' => 'Invalid 2FA code']]);
        exit();
    }
}
