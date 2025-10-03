<?php
require_once 'error_handler.php';

header('Content-Type: application/json');

include("db_connect.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and retrieve form data
    $firstName = trim($_POST['firstName'] ?? '');
    $middleName = trim($_POST['middleName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $houseNo = trim($_POST['houseNo'] ?? '');
    $streetName = trim($_POST['streetName'] ?? '');
    $barangay = trim($_POST['barangay'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    $agreeTerms = $_POST['agreeTerms'] ?? '';

    $errors = [];

    // Validation for required fields
    if (empty($firstName)) {
        $errors[] = ['field' => 'firstName', 'message' => 'First name is required'];
    }
    if (empty($lastName)) {
        $errors[] = ['field' => 'lastName', 'message' => 'Last name is required'];
    }
    if (empty($email)) {
        $errors[] = ['field' => 'email', 'message' => 'Email is required'];
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = ['field' => 'email', 'message' => 'Invalid email format'];
    }
    if (empty($city)) {
        $errors[] = ['field' => 'city', 'message' => 'City is required'];
    }

    // Password validation
    if (empty($password)) {
        $errors[] = ['field' => 'password', 'message' => 'Password is required'];
    } elseif (strlen($password) < 8) {
        $errors[] = ['field' => 'password', 'message' => 'Password must be at least 8 characters long'];
    }

    if (empty($confirmPassword)) {
        $errors[] = ['field' => 'confirmPassword', 'message' => 'Please confirm your password'];
    } elseif ($password !== $confirmPassword) {
        $errors[] = ['field' => 'confirmPassword', 'message' => 'Passwords do not match'];
    }

    if (empty($agreeTerms)) {
        $errors[] = ['field' => 'agreeTerms', 'message' => 'You must agree to the Terms of Service and Privacy Policy'];
    }

    // If there are validation errors, return them
    if (!empty($errors)) {
        echo json_encode(['error' => $errors]);
        exit();
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT USER_ID FROM USER WHERE EMAIL = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $errors[] = ['field' => 'email', 'message' => 'Email already registered. Please login.'];
        echo json_encode(['error' => $errors]);
        $stmt->close();
        exit();
    }
    $stmt->close();

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert into database
    $sql = "INSERT INTO USER (
        FIRST_NAME, 
        MIDDLE_NAME, 
        LAST_NAME, 
        EMAIL, 
        PHONE, 
        PASSWORD, 
        HOUSE_NO, 
        STREET_NAME, 
        BARANGAY, 
        CITY, 
        ROLE,
        CREATED_AT
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'customer', NOW())";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssss", 
        $firstName, 
        $middleName, 
        $lastName, 
        $email, 
        $phone, 
        $hashedPassword, 
        $houseNo, 
        $streetName, 
        $barangay, 
        $city
    );

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        // Generic error for database insertion failure
        $errors[] = ['message' => 'Error creating account. Please try again later.'];
        echo json_encode(['error' => $errors]);
    }

    $stmt->close();
    $conn->close();
}
?>