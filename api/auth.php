<?php
/**
 * Authentication API
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth_helper.php';
require_once __DIR__ . '/../includes/mailer.php';

header('Content-Type: application/json; charset=utf-8');

$pdo = getDbConnection();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? ($_POST['action'] ?? '');

if ($method === 'GET' && $action === 'check') {
    jsonResponse([
        'authenticated' => isAdminLoggedIn(),
        'user' => $_SESSION['admin_user'] ?? null,
        'email' => $_SESSION['admin_email'] ?? null,
        'db_type' => $GLOBALS['ACTIVE_DB_TYPE'] ?? 'unknown'
    ]);
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $postAction = $input['action'] ?? $action;

    if ($postAction === 'login') {
        $username = trim($input['username'] ?? '');
        $password = trim($input['password'] ?? '');

        if (empty($username) || empty($password)) {
            jsonResponse(['success' => false, 'error' => 'Username and password are required.'], 400);
        }

        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $otpEmail = ADMIN_OTP_EMAIL ?: $user['email'];

            if (defined('ENABLE_LOGIN_OTP') && ENABLE_LOGIN_OTP) {
                // Generate 6-digit OTP
                $otpCode = str_pad(strval(random_int(100000, 999999)), 6, '0', STR_PAD_LEFT);

                $_SESSION['pending_otp'] = [
                    'code' => $otpCode,
                    'expires' => time() + 600, // 10 minutes
                    'user_id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $otpEmail,
                    'attempts' => 0,
                    'sent_at' => time()
                ];

                // Send OTP email
                sendAdminLoginOtp($otpCode, $otpEmail);

                // Mask email for security display (e.g., br***@gmail.com)
                $parts = explode('@', $otpEmail);
                $maskedName = substr($parts[0], 0, 2) . str_repeat('*', max(3, strlen($parts[0]) - 2));
                $maskedEmail = $maskedName . '@' . ($parts[1] ?? 'gmail.com');

                jsonResponse([
                    'success' => true,
                    'require_otp' => true,
                    'email' => $maskedEmail,
                    'full_email' => $otpEmail,
                    'message' => "Verification code dispatched to {$maskedEmail}."
                ]);
            } else {
                // Direct login without OTP
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_user'] = $user['username'];
                $_SESSION['admin_email'] = $user['email'];

                jsonResponse([
                    'success' => true,
                    'require_otp' => false,
                    'message' => 'Login successful',
                    'user' => [
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'email' => $user['email']
                    ]
                ]);
            }
        } else {
            jsonResponse(['success' => false, 'error' => 'Invalid username or password.'], 401);
        }
    }

    if ($postAction === 'verify_otp') {
        $enteredOtp = trim($input['otp'] ?? '');

        if (empty($_SESSION['pending_otp'])) {
            jsonResponse(['success' => false, 'error' => 'No active login session. Please sign in again.'], 400);
        }

        $sessionOtp = $_SESSION['pending_otp'];

        if (time() > $sessionOtp['expires']) {
            unset($_SESSION['pending_otp']);
            jsonResponse(['success' => false, 'error' => 'Verification code has expired. Please sign in again.'], 400);
        }

        if ($sessionOtp['attempts'] >= 5) {
            unset($_SESSION['pending_otp']);
            jsonResponse(['success' => false, 'error' => 'Too many failed attempts. Please sign in again.'], 400);
        }

        $_SESSION['pending_otp']['attempts']++;

        if ($enteredOtp === $sessionOtp['code']) {
            // OTP is valid! Finalize login
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $sessionOtp['user_id'];
            $_SESSION['admin_user'] = $sessionOtp['username'];
            $_SESSION['admin_email'] = $sessionOtp['email'];
            unset($_SESSION['pending_otp']);

            jsonResponse([
                'success' => true,
                'message' => 'Verification successful! Welcome back.',
                'user' => [
                    'username' => $_SESSION['admin_user'],
                    'email' => $_SESSION['admin_email']
                ]
            ]);
        } else {
            $remaining = 5 - $_SESSION['pending_otp']['attempts'];
            jsonResponse([
                'success' => false,
                'error' => "Invalid code. {$remaining} attempts remaining."
            ], 400);
        }
    }

    if ($postAction === 'resend_otp') {
        if (empty($_SESSION['pending_otp'])) {
            jsonResponse(['success' => false, 'error' => 'No pending login found. Please sign in again.'], 400);
        }

        $lastSent = $_SESSION['pending_otp']['sent_at'] ?? 0;
        if (time() - $lastSent < 30) {
            $waitSeconds = 30 - (time() - $lastSent);
            jsonResponse(['success' => false, 'error' => "Please wait {$waitSeconds} seconds before requesting a new code."], 429);
        }

        $newOtp = str_pad(strval(random_int(100000, 999999)), 6, '0', STR_PAD_LEFT);
        $_SESSION['pending_otp']['code'] = $newOtp;
        $_SESSION['pending_otp']['expires'] = time() + 600;
        $_SESSION['pending_otp']['sent_at'] = time();

        sendAdminLoginOtp($newOtp, $_SESSION['pending_otp']['email']);

        jsonResponse([
            'success' => true,
            'message' => 'A fresh verification code has been dispatched to your email!'
        ]);
    }

    if ($postAction === 'logout') {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        jsonResponse(['success' => true, 'message' => 'Logged out successfully']);
    }

    if ($postAction === 'update_profile') {
        requireAdminAuth();

        $adminId = $_SESSION['admin_id'];
        $newUsername = trim($input['username'] ?? '');
        $newEmail = trim($input['email'] ?? '');
        $currentPassword = trim($input['current_password'] ?? '');
        $newPassword = trim($input['new_password'] ?? '');

        if (empty($newUsername) || empty($newEmail)) {
            jsonResponse(['success' => false, 'error' => 'Username and email are required.'], 400);
        }

        // Fetch current user
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE id = ?");
        $stmt->execute([$adminId]);
        $user = $stmt->fetch();

        if (!$user) {
            jsonResponse(['success' => false, 'error' => 'Admin user not found.'], 404);
        }

        // If changing password, verify current password
        if (!empty($newPassword)) {
            if (empty($currentPassword) || !password_verify($currentPassword, $user['password_hash'])) {
                jsonResponse(['success' => false, 'error' => 'Current password is incorrect.'], 400);
            }
            if (strlen($newPassword) < 6) {
                jsonResponse(['success' => false, 'error' => 'New password must be at least 6 characters.'], 400);
            }
            $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
            $updateStmt = $pdo->prepare("UPDATE admin_users SET username = ?, email = ?, password_hash = ? WHERE id = ?");
            $updateStmt->execute([$newUsername, $newEmail, $newHash, $adminId]);
        } else {
            $updateStmt = $pdo->prepare("UPDATE admin_users SET username = ?, email = ? WHERE id = ?");
            $updateStmt->execute([$newUsername, $newEmail, $adminId]);
        }

        $_SESSION['admin_user'] = $newUsername;
        $_SESSION['admin_email'] = $newEmail;

        jsonResponse([
            'success' => true,
            'message' => 'Profile updated successfully!',
            'user' => [
                'username' => $newUsername,
                'email' => $newEmail
            ]
        ]);
    }
}

jsonResponse(['error' => 'Invalid endpoint or request method'], 400);
