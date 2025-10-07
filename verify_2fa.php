<?php
require_once 'error_handler.php';

header('Content-Type: application/json');
session_start();
include("db_connect.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit();
}

$two_fa_code = trim($_POST['2fa-code'] ?? '');

if (empty($two_fa_code)) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => '2FA code is required']);
    exit();
}

if (!isset($_SESSION['2fa_pending']) || !isset($_SESSION['2fa_user_id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['status' => 'error', 'message' => '2FA process not initiated or session expired. Please try logging in again.']);
    exit();
}

$user_id = intval($_SESSION['2fa_user_id']);

try {
    // --- SECURITY FIX: Verify against database, not session ---
    $stmt = $conn->prepare("SELECT FIRST_NAME, ROLE, TFA_CODE, TFA_EXPIRY FROM USER WHERE USER_ID = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows !== 1) {
        throw new Exception('User not found.', 404);
    }

    $user = $result->fetch_assoc();

    // Check if code exists and is not expired
    if (is_null($user['TFA_CODE']) || strtotime($user['TFA_EXPIRY']) < time()) {
        throw new Exception('2FA code has expired. Please log in again to receive a new one.', 401);
    }

    // Securely compare the codes
    if (!hash_equals($user['TFA_CODE'], $two_fa_code)) {
        throw new Exception('Invalid 2FA code.', 401);
    }

    // --- Successful Verification: Finalize Login ---

    // 1. Clear the 2FA code from the database to prevent reuse
    $clear_stmt = $conn->prepare("UPDATE USER SET TFA_CODE = NULL, TFA_EXPIRY = NULL, last_login_at = NOW() WHERE USER_ID = ?");
    $clear_stmt->bind_param("i", $user_id);
    $clear_stmt->execute();

    // 2. Regenerate session ID for security
    session_regenerate_id(true);

    // 3. Store user data in the new session
    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_first_name'] = $user['FIRST_NAME'];
    $_SESSION['role'] = $user['ROLE']; 

    // 4. Clear temporary 2FA session flags
    unset($_SESSION['2fa_pending']);
    unset($_SESSION['2fa_user_id']);
    unset($_SESSION['email']); // Also clear email if it was stored

    echo json_encode(['status' => 'success', 'message' => 'Verification successful.']);

} catch (Exception $e) {
    $code = $e->getCode() >= 400 ? $e->getCode() : 500;
    http_response_code($code);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
?>