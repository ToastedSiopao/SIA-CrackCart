 <?php
require_once 'error_handler.php';

header('Content-Type: application/json');

include("db_connect.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $firstName = mysqli_real_escape_string($conn, $_POST['firstName']);
    $middleName = mysqli_real_escape_string($conn, $_POST['middleName'] ?? '');
    $lastName = mysqli_real_escape_string($conn, $_POST['lastName']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone'] ?? '');
    $houseNo = mysqli_real_escape_string($conn, $_POST['houseNo'] ?? '');
    $streetName = mysqli_real_escape_string($conn, $_POST['streetName'] ?? '');
    $barangay = mysqli_real_escape_string($conn, $_POST['barangay'] ?? '');
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

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

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Check if email already exists
    $checkEmail = "SELECT * FROM USER WHERE EMAIL='$email'";
    $result = mysqli_query($conn, $checkEmail);

    if (mysqli_num_rows($result) > 0) {
        echo json_encode(['error' => ['message' => 'Email already registered. Please login.']]);
        exit();
    }

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
    ) VALUES (
        '$firstName', 
        '$middleName', 
        '$lastName', 
        '$email', 
        '$phone', 
        '$hashedPassword', 
        '$houseNo', 
        '$streetName', 
        '$barangay', 
        '$city', 
        'customer',
        NOW()
    )";

    if (mysqli_query($conn, $sql)) {
        echo json_encode(['success' => true]);
        exit();
    } else {
        echo json_encode(['error' => ['message' => 'Error creating account: ' . mysqli_error($conn)]]);
        exit();
    }
}
?>