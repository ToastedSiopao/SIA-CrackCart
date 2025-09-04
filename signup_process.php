<?php
include("db_connect.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullName = mysqli_real_escape_string($conn, $_POST['fullName']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

    // Check if passwords match
    if ($password !== $confirmPassword) {
        header("Location: signup.php?error=" . urlencode("Passwords do not match"));
        exit();
    }

    // Check password length
    if (strlen($password) < 8) {
        header("Location: signup.php?error=" . urlencode("Password must be at least 8 characters long"));
        exit();
    }

    // Hash the password for security
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Check if email already exists
    $checkEmail = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($conn, $checkEmail);

    if (mysqli_num_rows($result) > 0) {
        header("Location: signup.php?error=" . urlencode("Email already registered. Please login."));
        exit();
    }

    // Insert user into DB
    $sql = "INSERT INTO users (full_name, email, phone, password, role, status, created_at) 
            VALUES ('$fullName', '$email', '$phone', '$hashedPassword', 'customer', 'active', NOW())";

    if (mysqli_query($conn, $sql)) {
        header("Location: signup.php?success=" . urlencode("Account created successfully! Please login."));
        exit();
    } else {
        header("Location: signup.php?error=" . urlencode("Error creating account: " . mysqli_error($conn)));
        exit();
    }
}
?>