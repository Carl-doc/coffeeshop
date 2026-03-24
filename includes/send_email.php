<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/PHPMailer.php';
require __DIR__ . '/PHPMailer/SMTP.php';
require __DIR__ . '/PHPMailer/Exception.php';

function sendVerificationEmail($email, $code) {

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;

        $mail->Username = 'cafecruise2026@gmail.com';
        $mail->Password = 'nudj jllr rqmz kdbs'; // CHANGE THIS

        $mail->SMTPSecure = 'tls'; // ✅ FIXED
        $mail->Port = 587;

        $mail->setFrom('yourgmail@gmail.com', 'Cafe Cruise');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Cafe Cruise Verification Code';
        $mail->Body = "
            <h2>Your Verification Code</h2>
            <h1>$code</h1>
            <p>Enter this code to complete your registration.</p>
        ";

        return $mail->send();

    } catch (Exception $e) {
        return false;
    }
}