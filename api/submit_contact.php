<?php
// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer files
require '../vendor/phpmailer/phpmailer/src/Exception.php';
require '../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require '../vendor/phpmailer/phpmailer/src/SMTP.php';

require_once '../error_handler.php';
require_once '../config.php';

header('Content-Type: application/json');

// Basic validation
if (empty($_POST['name']) || empty($_POST['email']) || empty($_POST['subject']) || empty($_POST['message'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Please fill in all required fields.']);
    exit();
}

$name = htmlspecialchars($_POST['name']);
$email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
$subject = htmlspecialchars($_POST['subject']);
$message = htmlspecialchars($_POST['message']);

if (!$email) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid email address.']);
    exit();
}

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
    $mail->setFrom($email, $name);
    $mail->addAddress(SUPPORT_EMAIL, 'CrackCart Support');
    $mail->addReplyTo($email, $name);

    // Content
    $mail->isHTML(true);
    $mail->Subject = "New Contact Form Submission: " . $subject;
    $mail->Body    = "You have received a new message from your website contact form.<br><br>" .
                     "<b>Name:</b> {$name}<br>" .
                     "<b>Email:</b> {$email}<br>" .
                     "<b>Subject:</b> {$subject}<br><br>" .
                     "<b>Message:</b><br>" . nl2br($message);
    $mail->AltBody = "You have received a new message from your website contact form.\n\n" .
                     "Name: {$name}\n" .
                     "Email: {$email}\n" .
                     "Subject: {$subject}\n\n" .
                     "Message:\n" . $message;

    $mail->send();
    echo json_encode(['status' => 'success', 'message' => 'Message sent successfully!']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"]);
}
?>
