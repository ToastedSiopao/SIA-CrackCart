<?php
session_start();
include('../db_connect.php'); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        header("Location: index.php?error=Email and password are required.");
        exit();
    }

    $stmt = $conn->prepare("SELECT USER_ID, FIRST_NAME, PASSWORD, ROLE, ACCOUNT_STATUS, LOCK_EXPIRES_AT FROM USER WHERE EMAIL = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if ($user['ACCOUNT_STATUS'] === 'LOCKED') {
            if ($user['LOCK_EXPIRES_AT'] && strtotime($user['LOCK_EXPIRES_AT']) > time()) {
                $lock_expiry_formatted = date('F j, Y, g:i a', strtotime($user['LOCK_EXPIRES_AT']));
                header("Location: index.php?error=" . urlencode("Your account is locked until {$lock_expiry_formatted}."));
                exit();
            } else {
                $unlock_stmt = $conn->prepare("UPDATE USER SET ACCOUNT_STATUS = 'ACTIVE', LOCK_EXPIRES_AT = NULL WHERE USER_ID = ?");
                $unlock_stmt->bind_param("i", $user['USER_ID']);
                $unlock_stmt->execute();
                $unlock_stmt->close();
            }
        }

        if (password_verify($password, $user['PASSWORD'])) {
            if ($user['ROLE'] === 'driver') {
                $_SESSION['user_id'] = $user['USER_ID'];
                $_SESSION['first_name'] = $user['FIRST_NAME'];
                $_SESSION['role'] = $user['ROLE'];

                header("Location: driver_dashboard.php");
                exit();
            } else {
                header("Location: index.php?error=Access denied. You are not a driver.");
                exit();
            }
        } else {
            header("Location: index.php?error=Invalid email or password.");
            exit();
        }
    } else {
        header("Location: index.php?error=Invalid email or password.");
        exit();
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: index.php");
    exit();
}
?>