<?php
/**
 * Contact Inquiries & Messages API
 * Public POST for website visitors; Authenticated GET/POST for Admin.
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth_helper.php';

header('Content-Type: application/json; charset=utf-8');

$pdo = getDbConnection();
$method = $_SERVER['REQUEST_METHOD'];

// Public POST: Submit Inquiry from Website Contact Form
if ($method === 'POST' && (!isset($_GET['admin']) && !isset($_POST['admin_action']))) {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    // Check if this is an admin action
    if (isset($input['admin_action']) || isset($input['action']) && in_array($input['action'], ['update_status', 'delete'])) {
        // Admin action falls through to requireAdminAuth below
    } else {
        $name = trim($input['name'] ?? '');
        $email = trim($input['email'] ?? '');
        $service = trim($input['artwork_service'] ?? $input['service'] ?? 'Creative Project');
        $budget = trim($input['project_budget'] ?? $input['budget'] ?? '');
        $message = trim($input['message'] ?? '');

        if (empty($name) || empty($email) || empty($message)) {
            jsonResponse(['success' => false, 'error' => 'Please provide name, email, and message.'], 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            jsonResponse(['success' => false, 'error' => 'Please provide a valid email address.'], 400);
        }

        $stmt = $pdo->prepare("
            INSERT INTO messages (name, email, artwork_service, project_budget, message, status)
            VALUES (?, ?, ?, ?, ?, 'unread')
        ");
        $stmt->execute([$name, $email, $service, $budget, $message]);

        jsonResponse([
            'success' => true,
            'message' => 'Thank you! Your commission request has been recorded and delivered.',
            'id' => $pdo->lastInsertId()
        ]);
    }
}

// Below endpoints require Admin Login
requireAdminAuth();

if ($method === 'GET') {
    $status = $_GET['status'] ?? 'all';
    $params = [];

    $sql = "SELECT * FROM messages";
    if ($status !== 'all') {
        $sql .= " WHERE status = ?";
        $params[] = $status;
    }
    $sql .= " ORDER BY created_at DESC, id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $messages = $stmt->fetchAll();

    // Also get counts
    $unreadStmt = $pdo->query("SELECT COUNT(*) as unread_count FROM messages WHERE status = 'unread'");
    $unreadCount = $unreadStmt->fetch()['unread_count'] ?? 0;

    $totalStmt = $pdo->query("SELECT COUNT(*) as total_count FROM messages");
    $totalCount = $totalStmt->fetch()['total_count'] ?? 0;

    jsonResponse([
        'success' => true,
        'unread_count' => intval($unreadCount),
        'total_count' => intval($totalCount),
        'messages' => $messages
    ]);
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $action = $input['action'] ?? $input['admin_action'] ?? '';

    if ($action === 'update_status') {
        $id = intval($input['id'] ?? 0);
        $newStatus = trim($input['status'] ?? 'read');

        if (!$id || !in_array($newStatus, ['unread', 'read', 'archived'])) {
            jsonResponse(['success' => false, 'error' => 'Invalid parameters'], 400);
        }

        $stmt = $pdo->prepare("UPDATE messages SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $id]);

        jsonResponse(['success' => true, 'message' => "Message marked as {$newStatus}!"]);
    }

    if ($action === 'delete') {
        $id = intval($input['id'] ?? 0);
        if (!$id) {
            jsonResponse(['success' => false, 'error' => 'Missing message ID'], 400);
        }

        $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ?");
        $stmt->execute([$id]);

        jsonResponse(['success' => true, 'message' => 'Message deleted successfully!']);
    }
}

jsonResponse(['error' => 'Invalid request'], 400);
