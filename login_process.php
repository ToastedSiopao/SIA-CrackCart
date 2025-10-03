<?php
require_once 'error_handler.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

session_start();
include("db_connect.php");

// --- Rate Limiting ---
$max_attempts = 5;
$lockout_time = 300; // 5 minutes in seconds

if (isset($_SESSION['lockout_time']) && time() < $_SESSION['lockout_time']) {
    http_response_code(429); // Too Many Requests
    $remaining = $_SESSION['lockout_time'] - time();
    echo json_encode(['error' => ['message' => "Too many failed attempts. Please try again in {$remaining} seconds."]]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => ['message' => 'Invalid request method']]);
    exit();
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// --- Field Validation ---
if (empty($email) || empty($password)) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => [['field' => 'email', 'message' => 'Both email and password are required']]]);
    exit();
}

// --- User and Password Validation ---
try {
    $stmt = $conn->prepare("SELECT * FROM USER WHERE EMAIL = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['PASSWORD'])) {
            // Reset login attempts on success
            unset($_SESSION['login_attempts']);
            unset($_SESSION['lockout_time']);

            // --- 2FA Code Generation and Mailing ---
            $two_fa_code = rand(100000, 999999);
            $_SESSION['2fa_code'] = (string)$two_fa_code;
            $_SESSION['2fa_user_id'] = $user['USER_ID'];

            try {
                // (PHPMailer code remains the same as before)

                echo json_encode(['success' => true, 'two_factor' => true]);
                exit();

            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => ['message' => "Could not send 2FA code."]]);
                exit();
            }
        } else {
            // Increment failed attempts
            $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;

            if ($_SESSION['login_attempts'] >= $max_attempts) {
                $_SESSION['lockout_time'] = time() + $lockout_time;
                unset($_SESSION['login_attempts']); // Reset for next lockout cycle
                http_response_code(429);
                echo json_encode(['error' => ['message' => "Too many failed attempts. You are locked out for 5 minutes."]]);
            } else {
                http_response_code(401);
                echo json_encode(['error' => [['field' => 'password', 'message' => 'Incorrect password']]]);
            }
            exit();
        }
    } else {
        http_response_code(401);
        echo json_encode(['error' => [['field' => 'email', 'message' => 'Email not found']]]);
        exit();
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => ['message' => 'A database error occurred.']]);
    exit();
}
