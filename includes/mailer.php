<?php
/**
 * Email & OTP Notification Service
 * Dispatches secure verification codes to briantanael187@gmail.com
 */

require_once __DIR__ . '/../config.php';

function sendAdminLoginOtp(string $otpCode, string $recipientEmail = 'briantanael187@gmail.com'): bool {
    $subject = "🔐 Your Admin Verification Code: {$otpCode} — Brian Joshua Portfolio";

    // Clean Cyberpunk / High-end HTML Email
    $htmlBody = "
    <!DOCTYPE html>
    <html>
    <head>
      <meta charset='utf-8'>
      <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #08080b; color: #ffffff; margin: 0; padding: 24px; }
        .card { max-width: 520px; margin: 0 auto; background-color: #111116; border: 1px solid #26262e; border-radius: 8px; padding: 32px; box-shadow: 0 10px 30px rgba(0,0,0,0.6); }
        .badge { display: inline-block; font-size: 11px; letter-spacing: 2px; color: #ff1e2d; background: rgba(255, 30, 45, 0.12); border: 1px solid rgba(255, 30, 45, 0.3); padding: 4px 10px; border-radius: 4px; font-weight: bold; margin-bottom: 14px; }
        .title { font-size: 22px; font-weight: 800; color: #ffffff; margin: 0 0 12px 0; letter-spacing: 1px; }
        .desc { font-size: 14px; color: #a1a1aa; line-height: 1.6; margin: 0 0 24px 0; }
        .otp-box { background: #050507; border: 2px solid #ff1e2d; border-radius: 8px; padding: 18px; text-align: center; margin: 24px 0; box-shadow: 0 0 25px rgba(255, 30, 45, 0.25); }
        .otp-code { font-size: 36px; font-weight: 900; letter-spacing: 8px; color: #ffffff; font-family: 'Consolas', monospace; }
        .expiry { font-size: 12px; color: #71717a; text-align: center; margin-top: 8px; }
        .footer { font-size: 12px; color: #52525b; border-top: 1px solid #27272a; margin-top: 28px; padding-top: 18px; line-height: 1.5; }
      </style>
    </head>
    <body>
      <div class='card'>
        <div class='badge'>2-FACTOR AUTHENTICATION</div>
        <h1 class='title'>Brian Joshua Portfolio Admin</h1>
        <p class='desc'>A sign-in attempt was initiated for your Administrator account. Use the one-time verification code below to complete your login:</p>
        
        <div class='otp-box'>
          <div class='otp-code'>{$otpCode}</div>
          <div class='expiry'>⏱ Valid for the next 10 minutes</div>
        </div>
        
        <p class='desc' style='font-size: 13px;'>If you did not request this code, your credentials may be compromised. Please sign in and update your password immediately.</p>
        
        <div class='footer'>
          © " . date('Y') . " Brian Joshua Tanael — Portfolio Security System<br>
          Delivered to: <strong>{$recipientEmail}</strong>
        </div>
      </div>
    </body>
    </html>
    ";

    $host = $_SERVER['SERVER_NAME'] ?? 'portfolio.local';
    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'From: Brian Joshua Portfolio Security <no-reply@' . $host . '>',
        'Reply-To: ' . $recipientEmail,
        'X-Priority: 1 (Highest)',
        'X-Mailer: PHP/' . phpversion()
    ];

    // Attempt PHP mail()
    $sent = @mail($recipientEmail, $subject, $htmlBody, implode("\r\n", $headers));

    // Save latest OTP to local log for dev convenience / backup
    $logDir = __DIR__ . '/../data';
    if (is_dir($logDir) || @mkdir($logDir, 0755, true)) {
        @file_put_contents($logDir . '/latest_otp.txt', "Time: " . date('Y-m-d H:i:s') . "\nCode: {$otpCode}\nRecipient: {$recipientEmail}\nMail sent status: " . ($sent ? 'Yes' : 'No') . "\n");
    }

    return $sent;
}
