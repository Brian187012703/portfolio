<?php
/**
 * Email & OTP Notification Service
 * Dispatches secure verification codes to briantanael187@gmail.com
 */

require_once __DIR__ . '/../config.php';

function sendAdminLoginOtp(string $otpCode, string $recipientEmail = 'briantanael187@gmail.com'): array {
    $subject = "🔐 Your Admin Verification Code: {$otpCode} — Brian Joshua Portfolio";

    // 1. Dispatch via FormSubmit API (Provides live Gmail delivery even without local SMTP server)
    $formSubmitSuccess = false;
    $formSubmitMessage = '';

    try {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? 'localhost');
        $origin = $protocol . $host;
        $referer = $origin . ($_SERVER['REQUEST_URI'] ?? '/portfolio/admin/login.php');

        $payload = [
            '_subject' => "🔐 Admin Verification Code: {$otpCode} — Brian Joshua Portfolio",
            'VERIFICATION_CODE' => $otpCode,
            'STATUS' => 'Valid for 10 minutes',
            'SYSTEM' => 'Brian Joshua Portfolio — 2-Factor Authentication',
            'ACCOUNT' => 'Administrator',
            'RECIPIENT' => $recipientEmail,
            '_captcha' => 'false'
        ];

        $ch = curl_init('https://formsubmit.co/ajax/' . urlencode($recipientEmail));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            'Origin: ' . $origin,
            'Referer: ' . $referer,
            'User-Agent: Mozilla/5.0 (Portfolio-Security/2.0)'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 6);
        $resp = curl_exec($ch);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($resp) {
            $json = json_decode($resp, true);
            if (isset($json['success']) && ($json['success'] === 'true' || $json['success'] === true)) {
                $formSubmitSuccess = true;
            } elseif (isset($json['message']) && stripos($json['message'], 'activation') !== false) {
                $formSubmitMessage = 'Activation email sent to Gmail by FormSubmit';
            }
        }
    } catch (Exception $e) {
        // Fall through to native mail
    }

    // 2. Dispatch via native PHP mail() (Works out-of-the-box on HostForge / cPanel / Linux Apache)
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
        
        <p class='desc' style='font-size: 13px;'>If you did not request this code, please update your password immediately.</p>
        
        <div class='footer'>
          © " . date('Y') . " Brian Joshua Tanael — Portfolio Security System<br>
          Delivered to: <strong>{$recipientEmail}</strong>
        </div>
      </div>
    </body>
    </html>
    ";

    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'From: Brian Joshua Portfolio Security <no-reply@' . ($_SERVER['SERVER_NAME'] ?? 'portfolio.local') . '>',
        'Reply-To: ' . $recipientEmail,
        'X-Priority: 1 (Highest)',
        'X-Mailer: PHP/' . phpversion()
    ];

    $phpMailSuccess = @mail($recipientEmail, $subject, $htmlBody, implode("\r\n", $headers));

    // 3. Save latest OTP to local file for backup and local development reference
    $logDir = __DIR__ . '/../data';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    @file_put_contents($logDir . '/latest_otp.txt', "Time: " . date('Y-m-d H:i:s') . "\nCode: {$otpCode}\nRecipient: {$recipientEmail}\nFormSubmit: " . ($formSubmitSuccess ? 'Delivered' : ($formSubmitMessage ?: 'Attempted')) . "\nPHP Mail: " . ($phpMailSuccess ? 'Sent' : 'Offline/Not configured on localhost') . "\n");

    return [
        'success' => $formSubmitSuccess || $phpMailSuccess,
        'formsubmit' => $formSubmitSuccess,
        'php_mail' => $phpMailSuccess,
        'note' => $formSubmitMessage
    ];
}
