<?php
require_once 'error_handler.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

session_start();
include("db_connect.php"); // $conn is an object

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Find active user by email - updated to use prepared statements
    $sql = "SELECT * FROM USER WHERE EMAIL=? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['PASSWORD'])) {
            // Generate 6-digit 2FA code
            $two_fa_code = rand(100000, 999999);

            // Save temporary values in session
            $_SESSION['2fa_code'] = (string)$two_fa_code;
            $_SESSION['2fa_user_id'] = $user['USER_ID'];

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
                $mail->addAddress($user['EMAIL'], $user['FIRST_NAME'] . ' ' . $user['LAST_NAME']);
                $mail->addReplyTo('yourgmail@gmail.com', 'Support Team');

                // Email content
                $mail->isHTML(true);
                $mail->Subject = 'Your CrackCart 2FA Code';
                $mail->Body    = "Hello <b>{$user['FIRST_NAME']} {$user['LAST_NAME']}</b>,<br><br>
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
?>