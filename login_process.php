<?php
require_once 'error_handler.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

session_start();
include("db_connect.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email='$email' AND status='active'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user['password'])) {
            $two_fa_code = rand(100000, 999999);
            $_SESSION['2fa_code'] = $two_fa_code;
            $_SESSION['2fa_user_id'] = $user['id'];

            $mail = new PHPMailer(true);

            try {
                //Server settings - **IMPORTANT: REPLACE WITH YOUR OWN CREDENTIALS**
                $mail->isSMTP();
                $mail->Host       = 'smtp.example.com';  // Set the SMTP server to send through
                $mail->SMTPAuth   = true;
                $mail->Username   = 'your_email@example.com'; // SMTP username
                $mail->Password   = 'your_password';        // SMTP password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                //Recipients
                $mail->setFrom('from@example.com', 'Your App Name');
                $mail->addAddress($user['email'], $user['full_name']);

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Your Two-Factor Authentication Code';
                $mail->Body    = "Your 2FA code is: <b>{$two_fa_code}</b>";
                $mail->AltBody = "Your 2FA code is: {$two_fa_code}";

                $mail->send();
                echo json_encode(['success' => true, 'two_factor' => true]);
            } catch (Exception $e) {
                echo json_encode(['error' => ['message' => "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"]]);
            }
            exit();
        } else {
            echo json_encode(['error' => ['message' => 'Invalid email or password']]);
            exit();
        }
    } else {
        echo json_encode(['error' => ['message' => 'Invalid email or password']]);
        exit();
    }
}
?>