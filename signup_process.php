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
    if (empty($phone)) {
        $errors[] = ['field' => 'phone', 'message' => 'Phone number is required'];
    }
    if (empty($houseNo)) {
        $errors[] = ['field' => 'houseNo', 'message' => 'House number is required'];
    }
    if (empty($streetName)) {
        $errors[] = ['field' => 'streetName', 'message' => 'Street name is required'];
    }
    if (empty($barangay)) {
        $errors[] = ['field' => 'barangay', 'message' => 'Barangay is required'];
    }
    if (empty($city)) {
        $errors[] = ['field' => 'city', 'message' => 'City is required'];
    }
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

    // Start transaction
    $conn->begin_transaction();

    try {
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert into USER table
        $sql_user = "INSERT INTO USER (FIRST_NAME, MIDDLE_NAME, LAST_NAME, EMAIL, PHONE, PASSWORD, HOUSE_NO, STREET_NAME, BARANGAY, CITY, ROLE, CREATED_AT) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'customer', NOW())";
        $stmt_user = $conn->prepare($sql_user);
        $stmt_user->bind_param("ssssssssss", $firstName, $middleName, $lastName, $email, $phone, $hashedPassword, $houseNo, $streetName, $barangay, $city);

        if (!$stmt_user->execute()) {
            throw new Exception("Error creating user account.");
        }

        // Get the new user's ID
        $new_user_id = $conn->insert_id;
        $stmt_user->close();

        // Insert into user_addresses table
        $address_line1 = $houseNo . ' ' . $streetName;
        $address_line2 = $barangay;
        $state = $city; // Assumption as state is not provided
        $country = "Philippines"; // Default country
        $address_type = "shipping"; // Default address type
        $zip_code = ""; // Not provided

        $sql_address = "INSERT INTO user_addresses (user_id, address_line1, address_line2, city, state, zip_code, country, address_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt_address = $conn->prepare($sql_address);
        $stmt_address->bind_param("isssssss", $new_user_id, $address_line1, $address_line2, $city, $state, $zip_code, $country, $address_type);

        if (!$stmt_address->execute()) {
            throw new Exception("Error saving default address.");
        }
        $stmt_address->close();

        // If both inserts are successful, commit the transaction
        $conn->commit();
        echo json_encode(['success' => true]);

    } catch (Exception $e) {
        // If any part fails, roll back the transaction
        $conn->rollback();
        $errors[] = ['message' => $e->getMessage()];
        http_response_code(500);
        echo json_encode(['error' => $errors]);
    }

    $conn->close();
}
?>