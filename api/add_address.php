<?php
require_once '../session_handler.php';
require_once '../db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'User not logged in']);
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $region_code = $_POST['region'] ?? null;
    $province_code = $_POST['province'] ?? null;
    $city_code = $_POST['city'] ?? null;
    $barangay_code = $_POST['barangay'] ?? null;
    $street = $_POST['street'] ?? null;
    $zipcode = $_POST['zipcode'] ?? null;

    $region_text = $_POST['region_text'] ?? null;
    $province_text = $_POST['province_text'] ?? null;
    $city_text = $_POST['city_text'] ?? null;
    $barangay_text = $_POST['barangay_text'] ?? null;

    if (!$region_code || !$province_code || !$city_code || !$barangay_code || !$street) {
        echo json_encode(['success' => false, 'message' => 'Incomplete address details']);
        exit;
    }

    $stmt = $conn->prepare(
        "INSERT INTO user_addresses (user_id, address_line1, country, region, province, city, barangay, zip_code, region_code, province_code, city_code, barangay_code, address_type, is_default) " .
        "VALUES (?, ?, 'Philippines', ?, ?, ?, ?, ?, ?, ?, ?, ?, 'shipping', 0)"
    );

    if ($stmt === false) {
        echo json_encode(['success' => false, 'message' => 'Prepare statement failed: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("issssssssss", 
        $user_id, 
        $street, 
        $region_text, 
        $province_text, 
        $city_text, 
        $barangay_text, 
        $zipcode, 
        $region_code, 
        $province_code, 
        $city_code, 
        $barangay_code
    );

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Address added successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Execute failed: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
