<?php
require_once 'error_handler.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

session_start();
include("db_connect.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Find active user by email
    $sql = "SELECT * FROM users WHERE email='$email' AND status='active' LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user['password'])) {
            // Generate 6-digit 2FA code
            $two_fa_code = rand(100000, 999999);

            // Save temporary values in session
            $_SESSION['2fa_code'] = (string)$two_fa_code;
            $_SESSION['2fa_user_id'] = $user['user_id']; 

            // Send code by email
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'qesnmiana@tip.edu.ph';  // your Gmail
                $mail->Password   = 'fjomlacwktwdssvs';    // 16-char Gmail app password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                // Recipients
                $mail->setFrom('yourgmail@gmail.com', 'CrackCart');
                $mail->addAddress($user['email'], $user['full_name']);
                $mail->addReplyTo('yourgmail@gmail.com', 'Support Team');

                // Email content
                $mail->isHTML(true);
                $mail->Subject = 'Your CrackCart 2FA Code';
                $mail->Body    = "Hello <b>{$user['full_name']}</b>,<br><br>
                                  Your 2FA code is: <b>{$two_fa_code}</b><br><br>
                                  This code will expire in 5 minutes.";
                $mail->AltBody = "Your 2FA code is: {$two_fa_code}";

                $mail->send();

                // Tell frontend login succeeded → redirect to 2FA page
                echo json_encode(['success' => true, 'two_factor' => true]);
                exit();

            } catch (Exception $e) {
                echo json_encode(['error' => ['message' => "Mailer Error: {$mail->ErrorInfo}"]]);
                exit();
            }
        } else {
            echo json_encode(['error' => ['message' => 'Invalid email or password']]);
            exit();
        }
    } else {
        echo json_encode(['error' => ['message' => 'Invalid email or password']]);
        exit();
    }
}
