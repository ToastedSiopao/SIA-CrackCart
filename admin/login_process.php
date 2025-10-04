<?php
session_start();
include('../db_connect.php'); // Assumes db_connect.php is in the parent directory

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        header("Location: index.php?error=Email and password are required.");
        exit();
    }

    // Prepare and execute the statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT USER_ID, FIRST_NAME, PASSWORD, ROLE, ACCOUNT_STATUS, LOCK_EXPIRES_AT FROM USER WHERE EMAIL = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // First, check if the account is locked
        if ($user['ACCOUNT_STATUS'] === 'LOCKED') {
            if ($user['LOCK_EXPIRES_AT'] && strtotime($user['LOCK_EXPIRES_AT']) > time()) {
                $lock_expiry_formatted = date('F j, Y, g:i a', strtotime($user['LOCK_EXPIRES_AT']));
                header("Location: index.php?error=" . urlencode("Your account is locked until {$lock_expiry_formatted}."));
                exit();
            } else {
                // Lock has expired, so we can proceed and unlock it
                $unlock_stmt = $conn->prepare("UPDATE USER SET ACCOUNT_STATUS = 'ACTIVE', LOCK_EXPIRES_AT = NULL WHERE USER_ID = ?");
                $unlock_stmt->bind_param("i", $user['USER_ID']);
                $unlock_stmt->execute();
                $unlock_stmt->close();
            }
        }

        // Verify password and role using MD5 (for compatibility with older PHP)
        if ($user['PASSWORD'] === md5($password)) {
            if ($user['ROLE'] === 'admin') {
                // Set session variables
                $_SESSION['user_id'] = $user['USER_ID'];
                $_SESSION['first_name'] = $user['FIRST_NAME'];
                $_SESSION['role'] = $user['ROLE'];

                // Redirect to the admin dashboard
                header("Location: dashboard.php");
                exit();
            } else {
                // Not an admin
                header("Location: index.php?error=Access denied. You are not an administrator.");
                exit();
            }
        } else {
            // Invalid password
            header("Location: index.php?error=Invalid email or password.");
            exit();
        }
    } else {
        // User not found
        header("Location: index.php?error=Invalid email or password.");
        exit();
    }

    $stmt->close();
    $conn->close();
} else {
    // Not a POST request
    header("Location: index.php");
    exit();
}
?>