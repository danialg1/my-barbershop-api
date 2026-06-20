<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

function sendOTP($to_email, $otp_code, $type = 'register') {
    $mail = new PHPMailer(true);

    try {
        // Konfigurasi SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        
        // ==========================================
        // UBAH DENGAN EMAIL DAN APP PASSWORD ANDA
        // ==========================================
        $mail->Username   = 'danialgibran0@gmail.com'; // Ganti dengan Gmail Anda
        $mail->Password   = 'mypeqpocihasrufv';    // Ganti dengan App Password Gmail (bukan password biasa)
        // ==========================================
        
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Pengirim dan Penerima
        $mail->setFrom($mail->Username, 'My Barbershop');
        $mail->addAddress($to_email);

        // Konten Email
        $mail->isHTML(true);
        
        if ($type == 'forgot_password') {
            $mail->Subject = 'Kode Reset Password My Barbershop';
            $msg_body = "<p>Anda meminta untuk mengatur ulang kata sandi Anda. Berikut adalah kode verifikasi OTP Anda:</p>";
            $msg_footer = "<p style='color: #888; font-size: 12px; margin-top: 30px;'>Jika Anda tidak meminta reset password, abaikan email ini.</p>";
        } else {
            $mail->Subject = 'Kode Verifikasi My Barbershop';
            $msg_body = "<p>Terima kasih telah mendaftar! Berikut adalah kode verifikasi OTP Anda:</p>";
            $msg_footer = "<p style='color: #888; font-size: 12px; margin-top: 30px;'>Jika Anda tidak mendaftar di My Barbershop, abaikan email ini.</p>";
        }

        $mail->Body    = "
            <div style='font-family: Arial, sans-serif; max-width: 500px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 10px;'>
                <h2 style='text-align: center; color: #D4AF37;'>My Barbershop</h2>
                <p>Halo,</p>
                $msg_body
                <div style='text-align: center; margin: 20px 0;'>
                    <span style='font-size: 24px; font-weight: bold; background: #f3f4f6; padding: 10px 20px; border-radius: 5px; letter-spacing: 5px;'>$otp_code</span>
                </div>
                <p>Silakan masukkan kode tersebut di aplikasi untuk melanjutkan.</p>
                $msg_footer
            </div>
        ";
        $mail->AltBody = "Kode OTP Anda adalah: $otp_code";

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Uncomment baris di bawah untuk melihat pesan error saat debug
        // error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>
