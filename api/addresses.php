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

switch ($method) {
    case 'GET':
        $stmt = $conn->prepare("SELECT address_id, address_line1, address_line2, city, state, zip_code, country, is_default FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $addresses = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $addresses]);
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $address_line1 = $data['address_line1'];
        $address_line2 = $data['address_line2'] ?? null;
        $city = $data['city'];
        $state = $data['state'];
        $zip_code = $data['zip_code'];
        $country = $data['country'];
        $is_default = isset($data['is_default']) && $data['is_default'] ? 1 : 0;

        $conn->begin_transaction();
        try {
            if ($is_default) {
                $stmt = $conn->prepare("UPDATE user_addresses SET is_default = 0 WHERE user_id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
            }

            $stmt = $conn->prepare("INSERT INTO user_addresses (user_id, address_line1, address_line2, city, state, zip_code, country, is_default) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issssssi", $user_id, $address_line1, $address_line2, $city, $state, $zip_code, $country, $is_default);
            $stmt->execute();
            $conn->commit();
            echo json_encode(['status' => 'success', 'message' => 'Address added successfully.']);
        } catch (Exception $e) {
            $conn->rollback();
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to add address.']);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        $address_id = $data['address_id'];
        
        if (isset($data['make_default']) && $data['make_default']) {
            $conn->begin_transaction();
            try {
                $stmt = $conn->prepare("UPDATE user_addresses SET is_default = 0 WHERE user_id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();

                $stmt = $conn->prepare("UPDATE user_addresses SET is_default = 1 WHERE address_id = ? AND user_id = ?");
                $stmt->bind_param("ii", $address_id, $user_id);
                $stmt->execute();
                
                $conn->commit();
                echo json_encode(['status' => 'success', 'message' => 'Default address updated successfully.']);
            } catch (Exception $e) {
                $conn->rollback();
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to update default address.']);
            }
        } else {
            $address_line1 = $data['address_line1'];
            $address_line2 = $data['address_line2'] ?? null;
            $city = $data['city'];
            $state = $data['state'];
            $zip_code = $data['zip_code'];
            $country = $data['country'];

            $stmt = $conn->prepare("UPDATE user_addresses SET address_line1 = ?, address_line2 = ?, city = ?, state = ?, zip_code = ?, country = ? WHERE address_id = ? AND user_id = ?");
            $stmt->bind_param("ssssssii", $address_line1, $address__line2, $city, $state, $zip_code, $country, $address_id, $user_id);
            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Address updated successfully.']);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to update address.']);
            }
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents('php://input'), true);
        $address_id = $data['address_id'];

        // Check if the address is the default one
        $stmt = $conn->prepare("SELECT is_default FROM user_addresses WHERE address_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $address_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $address = $result->fetch_assoc();

        if ($address && $address['is_default']) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Cannot delete the default address.']);
            exit();
        }

        $stmt = $conn->prepare("DELETE FROM user_addresses WHERE address_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $address_id, $user_id);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode(['status' => 'success', 'message' => 'Address deleted successfully.']);
            } else {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Address not found or you do not have permission to delete it.']);
            }
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete address.']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
        break;
}

$conn->close();
?>