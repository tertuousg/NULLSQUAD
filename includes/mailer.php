<?php
declare(strict_types=1);

/*
| Email verification helper.
| If PHPMailer is installed, this function can send real mail.
| Without PHPMailer, the verification link is written to the PHP error log
| so the project remains easy to run in a classroom XAMPP setup.
*/

function verification_link(string $token): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    return $scheme . '://' . $host . url('verify_email.php?token=' . urlencode($token));
}

function send_verification_email(string $email, string $name, string $token): bool
{
    $link = verification_link($token);
    $subject = 'Verify your ' . APP_SHORT_NAME . ' account';
    $body = 'Hello ' . $name . ', please verify your account using this link: ' . $link;
    $autoload = __DIR__ . '/../vendor/autoload.php';

    if (is_file($autoload)) {
        require_once $autoload;
    }

    $mailerClass = '\\PHPMailer\\PHPMailer\\PHPMailer';

    if (class_exists($mailerClass)) {
        try {
            $mail = new $mailerClass(true);
            $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
            $mail->addAddress($email, $name);
            $mail->Subject = $subject;
            $mail->Body = $body;
            return $mail->send();
        } catch (Throwable $exception) {
            error_log('PHPMailer failed: ' . $exception->getMessage());
            return false;
        }
    }

    error_log($subject . ' - ' . $body);
    return true;
}
