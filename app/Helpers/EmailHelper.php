<?php

namespace App\Helpers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailHelper
{
    public static function sendEmail($to, $cc, $subject, $body, $toName, $ccName, $fromName, $from = 'admin@ketik-kan.com')
    {
        $mail = new PHPMailer(true);

        try {
            // Konfigurasi server
            $mail->SMTPDebug = 0;  //nonaktifkan debug -- tambahkan ini
            $mail->isSMTP();
            $mail->Host = 'ketik-kan.com'; // Ganti dengan SMTP server Anda
            $mail->SMTPAuth = true;
            $mail->Username = 'admin@ketik-kan.com'; // Ganti dengan SMTP username Anda
            $mail->Password = 'S@msungj256'; // Ganti dengan SMTP password Anda
            // $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->SMTPSecure = 'ssl';
            $mail->Port = 465; // 587

            // Pengaturan penerima dan pengirim
            $mail->setFrom($from, $fromName);
            $mail->addAddress($to, $toName); // Tambahkan penerima utama
            $mail->addAddress($cc, $ccName); // Tambahkan penerima cc

            // Konten email
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            $mail->send();
            return true;
        } catch (Exception $e) {
            // Menangani pengecualian sesuai kebutuhan Anda
            return false;
        }
    }
}
