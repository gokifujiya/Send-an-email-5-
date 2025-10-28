<?php
// Scripts/mailing-sample.php
$to      = 'fujiya20101857@yahoo.co.jp';          // TODO: change
$subject = 'Hello World';
$message = <<<MAIL
Hello There World,

This is a message to test sending messages.

Best regards,
Jupiter
MAIL;

$headers = implode("\r\n", [
    'From: TestApp <' . 'goki.fujiya@gmail.com' . '>',  // Gmail you relay through
    'Reply-To: ' . 'goki.fujiya@gmail.com',
    'X-Mailer: PHP/' . phpversion(),
]);

if (!mail($to, $subject, $message, $headers)) {
    fwrite(STDERR, "mail() returned false\n");
}
echo "Sent (mail())\n";
