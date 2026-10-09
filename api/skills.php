<?php
/**
 * Skills API
 * Public GET; Authenticated POST for Admin.
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth_helper.php';

header('Content-Type: application/json; charset=utf-8');

$pdo = getDbConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $pdo->query("SELECT * FROM skills ORDER BY sort_order ASC, id ASC");
    $skills = $stmt->fetchAll();
    jsonResponse(['success' => true, 'skills' => $skills]);
}

requireAdminAuth();

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $action = $input['action'] ?? 'add';

    if ($action === 'add') {
        $name = trim($input['name'] ?? '');
        $proficiency = trim($input['proficiency'] ?? 'Advanced');
        $sortOrder = intval($input['sort_order'] ?? 0);

        if (empty($name)) {
            jsonResponse(['success' => false, 'error' => 'Skill name is required'], 400);
        }

        $stmt = $pdo->prepare("INSERT INTO skills (name, proficiency, sort_order) VALUES (?, ?, ?)");
        $stmt->execute([$name, $proficiency, $sortOrder]);

        jsonResponse(['success' => true, 'message' => 'Skill added successfully!', 'id' => $pdo->lastInsertId()]);
    }

    if ($action === 'delete') {
        $id = intval($input['id'] ?? 0);
        if (!$id) {
            jsonResponse(['success' => false, 'error' => 'Missing skill ID'], 400);
        }

        $stmt = $pdo->prepare("DELETE FROM skills WHERE id = ?");
        $stmt->execute([$id]);

        jsonResponse(['success' => true, 'message' => 'Skill deleted successfully!']);
    }

    if ($action === 'save_all') {
        // Replace all skills in one bulk update
        $skillsList = $input['skills'] ?? [];
        if (!is_array($skillsList)) {
            jsonResponse(['success' => false, 'error' => 'Invalid skills list'], 400);
        }

        $pdo->beginTransaction();
        $pdo->exec("DELETE FROM skills");
        $stmt = $pdo->prepare("INSERT INTO skills (name, proficiency, sort_order) VALUES (?, ?, ?)");
        $order = 1;
        foreach ($skillsList as $s) {
            $name = trim($s['name'] ?? '');
            $prof = trim($s['proficiency'] ?? 'Advanced');
            if (!empty($name)) {
                $stmt->execute([$name, $prof, $order++]);
            }
        }
        $pdo->commit();

        jsonResponse(['success' => true, 'message' => 'Skills updated successfully!']);
    }
}

jsonResponse(['error' => 'Method not allowed'], 405);
