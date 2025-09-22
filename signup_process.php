<?php
require_once 'error_handler.php';

header('Content-Type: application/json');

include("db_connect.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $firstName = $_POST['firstName'] ?? '';
    $middleName = $_POST['middleName'] ?? '';
    $lastName = $_POST['lastName'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $houseNo = $_POST['houseNo'] ?? '';
    $streetName = $_POST['streetName'] ?? '';
    $barangay = $_POST['barangay'] ?? '';
    $city = $_POST['city'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';

    // Validation
    if ($password !== $confirmPassword) {
        echo json_encode(['error' => ['message' => 'Passwords do not match']]);
        exit();
    }

    if (strlen($password) < 8) {
        echo json_encode(['error' => ['message' => 'Password must be at least 8 characters long']]);
        exit();
    }

    if (empty($firstName) || empty($lastName) || empty($email) || empty($city)) {
        echo json_encode(['error' => ['message' => 'Please fill in all required fields']]);
        exit();
    }

    // Check if email already exists using a prepared statement
    $sql_check = "SELECT USER_ID FROM USER WHERE EMAIL=? LIMIT 1";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        echo json_encode(['error' => ['message' => 'Email already registered. Please login.']]);
        $stmt_check->close();
        exit();
    }
    $stmt_check->close();

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $role = 'customer'; // Default role

    // Insert into database using a prepared statement
    $sql_insert = "INSERT INTO USER (
        FIRST_NAME, MIDDLE_NAME, LAST_NAME, EMAIL, PHONE, PASSWORD, 
        HOUSE_NO, STREET_NAME, BARANGAY, CITY, ROLE, CREATED_AT
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("sssssssssss", 
        $firstName, 
        $middleName, 
        $lastName, 
        $email, 
        $phone, 
        $hashedPassword, 
        $houseNo, 
        $streetName, 
        $barangay, 
        $city, 
        $role
    );

    if ($stmt_insert->execute()) {
        echo json_encode(['success' => true]);
    } else {
        error_log("Signup Error: " . $stmt_insert->error);
        echo json_encode(['error' => ['message' => 'Error creating account. Please try again later.']]);
    }

    $stmt_insert->close();
    $conn->close();
    exit();
}
?>