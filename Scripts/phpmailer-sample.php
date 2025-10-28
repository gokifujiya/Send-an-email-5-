<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../Helpers/Settings.php';

// 🔹 Load .env once
\Helpers\Settings::load(__DIR__ . '/../.env');

$env = fn($k, $d = null) => \Helpers\Settings::env($k, $d);

$mail = new PHPMailer(true);

try {
    // Server
    $mail->isSMTP();
    $mail->Host       = '127.0.0.1';
    $mail->SMTPAuth   = false;
    $mail->Username   = $env('SMTP_USERNAME');   // from .env
    $mail->Password   = $env('SMTP_PASSWORD');   // from .env
    $mail->SMTPSecure = false;
    $mail->SMTPAutoTLS = false;        // <-- add this line
    $mail->Port       = 25;

    // From / To
    $mail->setFrom(
        $env('SMTP_FROM_EMAIL', $env('SMTP_USERNAME')),
        $env('SMTP_FROM_NAME',  'SpaceApp')
    );
    $mail->addAddress(
        $env('SMTP_TO_EMAIL', 'example@gmail.com'),
        $env('SMTP_TO_NAME',  'My User')
    );

    // Subject & body
    $mail->Subject = 'Hello World, From PHPMailer!';
    ob_start();
    include __DIR__ . '/../Views/mail/test.php';
    $mail->isHTML(true);
    $mail->Body = ob_get_clean();
    $mail->AltBody = file_get_contents(__DIR__ . '/../Views/mail/test.txt');

    $mail->send();
    echo "Message sent (PHPMailer)\n";
} catch (Exception $e) {
    echo "Message could not be sent. Error: {$mail->ErrorInfo}\n";
}

