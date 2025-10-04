<?php
session_start();
header("Content-Type: application/json");
include("../db_connect.php");

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'GET':
        $stmt = $conn->prepare("SELECT * FROM Address WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $addresses = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $addresses]);
        break;

    case 'POST':
        if (isset($data['street'], $data['city'], $data['state'], $data['zip_code'], $data['country'])) {
            $stmt = $conn->prepare("INSERT INTO Address (user_id, street, city, state, zip_code, country) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssss", $user_id, $data['street'], $data['city'], $data['state'], $data['zip_code'], $data['country']);
            if ($stmt->execute()) {
                $new_address_id = $conn->insert_id;
                http_response_code(201);
                echo json_encode(['status' => 'success', 'message' => 'Address added successfully.', 'address_id' => $new_address_id]);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to add address.']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid data provided.']);
        }
        break;

    case 'PUT':
        if (isset($data['address_id'], $data['street'], $data['city'], $data['state'], $data['zip_code'], $data['country'])) {
            $stmt = $conn->prepare("UPDATE Address SET street = ?, city = ?, state = ?, zip_code = ?, country = ? WHERE address_id = ? AND user_id = ?");
            $stmt->bind_param("sssssii", $data['street'], $data['city'], $data['state'], $data['zip_code'], $data['country'], $data['address_id'], $user_id);
            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Address updated successfully.']);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to update address.']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid data provided.']);
        }
        break;

    case 'DELETE':
        if (isset($data['address_id'])) {
            $stmt = $conn->prepare("DELETE FROM Address WHERE address_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $data['address_id'], $user_id);
            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Address deleted successfully.']);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete address.']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Address ID not provided.']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
        break;
}

$conn->close();
?>