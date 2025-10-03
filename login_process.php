<?php
require_once 'error_handler.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

session_start();
include("db_connect.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => ['message' => 'Invalid request method']]);
    exit();
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// --- Field Validation ---
if (empty($email)) {
    $errors[] = ['field' => 'email', 'message' => 'Email is required'];
}
if (empty($password)) {
    $errors[] = ['field' => 'password', 'message' => 'Password is required'];
}

if (!empty($errors)) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => $errors]);
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
            // --- 2FA Code Generation and Mailing ---
            $two_fa_code = rand(100000, 999999);
            $_SESSION['2fa_code'] = (string)$two_fa_code;
            $_SESSION['2fa_user_id'] = $user['USER_ID'];

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'qesnmiana@tip.edu.ph';
                $mail->Password   = 'fjomlacwktwdssvs';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                // Recipients
                $mail->setFrom('yourgmail@gmail.com', 'CrackCart');
                $mail->addAddress($email, $user['FIRST_NAME'] . ' ' . $user['LAST_NAME']);
                $mail->addReplyTo('yourgmail@gmail.com', 'Support Team');

                // Email content
                $mail->isHTML(true);
                $mail->Subject = 'Your CrackCart 2FA Code';
                $mail->Body    = "Hello <b>{$user['FIRST_NAME']} {$user['LAST_NAME']}</b>,<br><br>
                                  Your 2FA code is: <b>{$two_fa_code}</b><br><br>
                                  This code will expire in 5 minutes.";
                $mail->AltBody = "Your 2FA code is: {$two_fa_code}";

                $mail->send();

                echo json_encode(['success' => true, 'two_factor' => true]);
                exit();

            } catch (Exception $e) {
                http_response_code(500); // Internal Server Error
                echo json_encode(['error' => ['message' => "Could not send 2FA code. Please try again later. Mailer Error: {$mail->ErrorInfo}"]]);
                exit();
            }
        } else {
            http_response_code(401); // Unauthorized
            echo json_encode(['error' => [['field' => 'password', 'message' => 'Incorrect password']]]);
            exit();
        }
    } else {
        http_response_code(401); // Unauthorized
        echo json_encode(['error' => [['field' => 'email', 'message' => 'Email not found']]]);
        exit();
    }
} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => ['message' => 'A database error occurred. Please try again later.']]);
    exit();
}
