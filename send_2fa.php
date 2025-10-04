<?php
// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer files which are already in the vendor directory
require 'vendor/phpmailer/phpmailer/src/Exception.php';
require 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
require 'vendor/phpmailer/phpmailer/src/SMTP.php';

require_once 'error_handler.php';
require_once 'config.php';
session_start();
include("db_connect.php");

header('Content-Type: application/json');

// Check if user is authenticated for 2FA
if (!isset($_SESSION['2fa_pending']) || !isset($_SESSION['email'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['error' => '2FA process not initiated. Please log in again.']);
    exit();
}

$email = $_SESSION['email'];

// --- Generate and Store 2FA Code ---
try {
    $two_fa_code = rand(100000, 999999);
    $expiry_time = date('Y-m-d H:i:s', time() + 300); // 5-minute expiry

    $stmt = $conn->prepare("UPDATE USER SET TFA_CODE = ?, TFA_EXPIRY = ? WHERE EMAIL = ?");
    $stmt->bind_param("sss", $two_fa_code, $expiry_time, $email);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        throw new Exception('Could not find user record to update.');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    exit();
}

// --- Send 2FA Code via Email using PHPMailer and SMTP ---

$mail = new PHPMailer(true);

try {
    //Server settings
    $mail->isSMTP();
    $mail->Host       = 'smtp-relay.brevo.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    //Recipients
    $mail->setFrom('crackcart.auth@gmail.com', 'CrackCart Security');
    $mail->addAddress($email);

    //Content
    $mail->isHTML(true);
    $mail->Subject = 'Your Two-Factor Authentication Code';
    $mail->Body    = "Your 2FA code is: <b>{$two_fa_code}</b>. It will expire in 5 minutes.";
    $mail->AltBody = "Your 2FA code is: {$two_fa_code}. It will expire in 5 minutes.";

    $mail->send();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"]);
}

?>
