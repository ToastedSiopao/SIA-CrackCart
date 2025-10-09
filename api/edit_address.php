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
    $address_id = $_POST['address_id'] ?? null;
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

    if (!$address_id || !$region_code || !$province_code || !$city_code || !$barangay_code || !$street) {
        echo json_encode(['success' => false, 'message' => 'Incomplete address details']);
        exit;
    }

    $stmt = $conn->prepare(
        "UPDATE user_addresses SET " .
        "address_line1 = ?, region = ?, province = ?, city = ?, barangay = ?, zip_code = ?, " .
        "region_code = ?, province_code = ?, city_code = ?, barangay_code = ? " .
        "WHERE address_id = ? AND user_id = ?"
    );

    if ($stmt === false) {
        echo json_encode(['success' => false, 'message' => 'Prepare statement failed: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("ssssssssssii", 
        $street, 
        $region_text, 
        $province_text, 
        $city_text, 
        $barangay_text, 
        $zipcode, 
        $region_code, 
        $province_code, 
        $city_code, 
        $barangay_code, 
        $address_id, 
        $user_id
    );

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Address updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No changes were made or address not found']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Execute failed: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
