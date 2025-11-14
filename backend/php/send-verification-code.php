<?php

header('Content-Type: application/json');

require_once 'DatabaseConnector.php';

$composerAutoload = __DIR__ . '/../../vendor/autoload.php';
if (!file_exists($composerAutoload)) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'PHPMailer not installed. Please install via Composer: composer require phpmailer/phpmailer'
    ]);
    exit;
}

require $composerAutoload;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['email'])) {
        throw new Exception('Email is required');
    }

    $email = filter_var($input['email'], FILTER_VALIDATE_EMAIL);
    if (!$email) {
        throw new Exception('Invalid email address');
    }

    $verificationCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

    // Initialize PHPMailer
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'brian.ikubu@strathmore.edu';
    $mail->Password = 'vahi auht awkv kyri';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('brian.ikubu@strathmore.edu', 'Tel Aviv Hospital');

    // Recipient
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = 'Email Verification - Tel Aviv Hospital';
    $mail->Body = "
        <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; background: #f4f6f8; }
                    .container { max-width: 500px; margin: 20px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                    .header { text-align: center; margin-bottom: 30px; }
                    .header h1 { color: #145ea8; margin: 0; }
                    .code { background: #f0f4f8; text-align: center; padding: 20px; border-radius: 8px; margin: 20px 0; }
                    .code .number { font-size: 32px; font-weight: bold; color: #145ea8; letter-spacing: 5px; }
                    .footer { text-align: center; color: #6b7280; font-size: 12px; margin-top: 20px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>Tel Aviv Hospital</h1>
                        <p>Email Verification</p>
                    </div>
                    <p>Hello,</p>
                    <p>Thank you for signing up with Tel Aviv Hospital. Please use the verification code below to complete your registration:</p>
                    <div class='code'>
                        <div class='number'>$verificationCode</div>
                    </div>
                    <p>This code will expire in 5 minutes.</p>
                    <p>If you did not request this verification, please ignore this email.</p>
                    <div class='footer'>
                        <p>&copy; 2025 Tel Aviv Hospital. All rights reserved.</p>
                    </div>
                </div>
            </body>
        </html>
    ";

    // Plain text alternative
    $mail->AltBody = "Your verification code is: $verificationCode\n\nThis code will expire in 5 minutes.";

    $mail->send();

    echo json_encode([
        'success' => true,
        'code' => $verificationCode, // REMOVE THIS IN PRODUCTION
        'message' => 'Verification code sent to ' . $email
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
