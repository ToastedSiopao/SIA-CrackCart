<?php
require_once 'error_handler.php';
require 'vendor/autoload.php';
require_once 'config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

session_start();
header('Content-Type: application/json');
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

        // Check if account is deactivated
        if ($user['ACCOUNT_STATUS'] === 'INACTIVE') {
            http_response_code(403); // Forbidden
            echo json_encode(['error' => ['message' => 'Your account has been deactivated. Please contact support.']]);
            exit();
        }

        if (password_verify($password, $user['PASSWORD'])) {
            // Reset login attempts on success
            unset($_SESSION['login_attempts']);
            unset($_SESSION['lockout_time']);

            // --- 2FA Code Generation and Mailing ---
            $two_fa_code = rand(100000, 999999);

            // Store session values for verification
            $_SESSION['2fa_user'] = $user['USER_ID'];
            $_SESSION['2fa_code'] = (string)$two_fa_code;
            $_SESSION['2fa_expires'] = time() + 300; // 5 minutes
            error_log('Login session set: ' . print_r($_SESSION, true));

            $mail = new PHPMailer(true);

            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host       = 'smtp-relay.brevo.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = SMTP_USER;
                $mail->Password   = SMTP_PASS;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                // Recipients
                $mail->setFrom('crackcart.auth@gmail.com', 'CrackCart Security');
                $mail->addAddress($email);

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Your Two-Factor Authentication Code';
                $mail->Body    = "Your 2FA code is: <b>{$two_fa_code}</b>. It will expire in 5 minutes.";
                $mail->AltBody = "Your 2FA code is: {$two_fa_code}. It will expire in 5 minutes.";

                // Send the 2FA email
                $mail->send();

                // Respond to frontend
                echo json_encode(['success' => true, 'two_factor' => true, 'role' => $user['ROLE']]);
                exit();

            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => ['message' => "Could not send 2FA code. Mailer Error: {$mail->ErrorInfo}"]]);
                exit();
            }

        } else {
            // Increment failed attempts
            $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;

            if ($_SESSION['login_attempts'] >= $max_attempts) {
                $_SESSION['lockout_time'] = time() + $lockout_time;
                unset($_SESSION['login_attempts']); 

                // --- Create Notification for Account Lockout ---
                $notification_message = "Your account has been locked for 5 minutes due to too many failed login attempts.";
                $notification_type = 'SECURITY';
                $insert_notification_stmt = $conn->prepare("INSERT INTO NOTIFICATION (USER_ID, MESSAGE, TYPE) VALUES (?, ?, ?)");
                $insert_notification_stmt->bind_param("iss", $user['USER_ID'], $notification_message, $notification_type);
                $insert_notification_stmt->execute();

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
