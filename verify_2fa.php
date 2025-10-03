<?php
require_once 'error_handler.php';

header('Content-Type: application/json');
session_start();
include("db_connect.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => ['message' => 'Invalid request method']]);
    exit();
}

$two_fa_code = trim($_POST['2fa-code'] ?? '');

if (empty($two_fa_code)) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => [['field' => '2fa-code', 'message' => '2FA code is required']]]);
    exit();
}

if (!isset($_SESSION['2fa_code']) || !isset($_SESSION['2fa_user_id'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => ['message' => '2FA process not initiated or session expired. Please try logging in again.']]);
    exit();
}

// --- 2FA Code Validation ---
if ($two_fa_code === $_SESSION['2fa_code']) {
    $user_id = intval($_SESSION['2fa_user_id']);

    try {
        $stmt = $conn->prepare("SELECT * FROM USER WHERE USER_ID = ? LIMIT 1");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // --- Session Regeneration and User Data Storage ---
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['USER_ID'];
            $_SESSION['user_first_name'] = $user['FIRST_NAME'];
            $_SESSION['user_role'] = $user['ROLE'];

            // Clear 2FA data
            unset($_SESSION['2fa_code']);
            unset($_SESSION['2fa_user_id']);

            echo json_encode(['success' => true]);
            exit();
        } else {
            http_response_code(404); // Not Found
            echo json_encode(['error' => ['message' => 'User associated with 2FA not found']]);
            exit();
        }
    } catch (Exception $e) {
        http_response_code(500); // Internal Server Error
        echo json_encode(['error' => ['message' => 'A database error occurred.']]);
        exit();
    }
} else {
    http_response_code(401); // Unauthorized
    echo json_encode(['error' => [['field' => '2fa-code', 'message' => 'Invalid 2FA code']]]);
    exit();
}
