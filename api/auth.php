<?php
/**
 * Authentication API
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth_helper.php';

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
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_user'] = $user['username'];
            $_SESSION['admin_email'] = $user['email'];

            jsonResponse([
                'success' => true,
                'message' => 'Login successful',
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email']
                ]
            ]);
        } else {
            jsonResponse(['success' => false, 'error' => 'Invalid username or password.'], 401);
        }
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
