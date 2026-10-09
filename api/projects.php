<?php
/**
 * Projects API
 * Public GET for portfolio display; Authenticated POST/PUT/DELETE for admin.
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth_helper.php';

header('Content-Type: application/json; charset=utf-8');

$pdo = getDbConnection();
$method = $_SERVER['REQUEST_METHOD'];

// GET: Fetch Projects
if ($method === 'GET') {
    $id = isset($_GET['id']) ? intval($_GET['id']) : null;
    $category = $_GET['category'] ?? null;

    if ($id) {
        $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
        $stmt->execute([$id]);
        $project = $stmt->fetch();
        if (!$project) {
            jsonResponse(['success' => false, 'error' => 'Project not found'], 404);
        }
        $project['highlights'] = json_decode($project['highlights'] ?? '[]', true) ?: [];
        jsonResponse(['success' => true, 'project' => $project]);
    }

    $sql = "SELECT * FROM projects";
    $params = [];

    if (!isAdminLoggedIn()) {
        $sql .= " WHERE is_featured = 1";
    } else {
        $sql .= " WHERE 1=1";
    }

    if ($category && $category !== 'all') {
        $sql .= " AND category = ?";
        $params[] = $category;
    }

    $sql .= " ORDER BY sort_order ASC, id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $projects = $stmt->fetchAll();

    foreach ($projects as &$p) {
        $p['highlights'] = json_decode($p['highlights'] ?? '[]', true) ?: [];
    }

    jsonResponse([
        'success' => true,
        'count' => count($projects),
        'projects' => $projects
    ]);
}

// Write Operations Require Admin Login
requireAdminAuth();

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $action = $input['action'] ?? ($_GET['action'] ?? 'create');

    if ($action === 'create') {
        $title = trim($input['title'] ?? '');
        $category = trim($input['category'] ?? 'social');
        $categoryLabel = trim($input['category_label'] ?? '');
        $client = trim($input['client'] ?? '');
        $year = trim($input['year'] ?? date('Y'));
        $role = trim($input['role'] ?? '');
        $deliverables = trim($input['deliverables'] ?? '');
        $image = trim($input['image'] ?? 'assets/images/project_veloce.jpg');
        $description = trim($input['description'] ?? '');
        $liveDemoUrl = trim($input['live_demo_url'] ?? '#');
        $sortOrder = intval($input['sort_order'] ?? 0);
        $isFeatured = isset($input['is_featured']) ? intval($input['is_featured']) : 1;

        if (empty($title)) {
            jsonResponse(['success' => false, 'error' => 'Project title is required'], 400);
        }

        // Generate slug
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
        if (empty($slug)) {
            $slug = 'project-' . time();
        }

        // Process highlights
        $highlights = $input['highlights'] ?? [];
        if (is_string($highlights)) {
            $highlights = array_filter(array_map('trim', explode("\n", $highlights)));
        }
        $highlightsJson = json_encode(array_values($highlights));

        $stmt = $pdo->prepare("
            INSERT INTO projects (slug, title, category, category_label, client, year, role, deliverables, image, description, highlights, live_demo_url, sort_order, is_featured)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $slug, $title, $category, $categoryLabel, $client, $year, $role,
            $deliverables, $image, $description, $highlightsJson, $liveDemoUrl,
            $sortOrder, $isFeatured
        ]);

        $newId = $pdo->lastInsertId();
        jsonResponse([
            'success' => true,
            'message' => 'Project created successfully!',
            'id' => $newId
        ]);
    }

    if ($action === 'update') {
        $id = intval($input['id'] ?? 0);
        if (!$id) {
            jsonResponse(['success' => false, 'error' => 'Missing project ID'], 400);
        }

        $title = trim($input['title'] ?? '');
        $category = trim($input['category'] ?? 'social');
        $categoryLabel = trim($input['category_label'] ?? '');
        $client = trim($input['client'] ?? '');
        $year = trim($input['year'] ?? '');
        $role = trim($input['role'] ?? '');
        $deliverables = trim($input['deliverables'] ?? '');
        $image = trim($input['image'] ?? '');
        $description = trim($input['description'] ?? '');
        $liveDemoUrl = trim($input['live_demo_url'] ?? '#');
        $sortOrder = intval($input['sort_order'] ?? 0);
        $isFeatured = isset($input['is_featured']) ? intval($input['is_featured']) : 1;

        if (empty($title)) {
            jsonResponse(['success' => false, 'error' => 'Project title is required'], 400);
        }

        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));

        $highlights = $input['highlights'] ?? [];
        if (is_string($highlights)) {
            $highlights = array_filter(array_map('trim', explode("\n", $highlights)));
        }
        $highlightsJson = json_encode(array_values($highlights));

        $stmt = $pdo->prepare("
            UPDATE projects SET 
                slug = ?, title = ?, category = ?, category_label = ?, client = ?,
                year = ?, role = ?, deliverables = ?, image = ?, description = ?,
                highlights = ?, live_demo_url = ?, sort_order = ?, is_featured = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $slug, $title, $category, $categoryLabel, $client, $year, $role,
            $deliverables, $image, $description, $highlightsJson, $liveDemoUrl,
            $sortOrder, $isFeatured, $id
        ]);

        jsonResponse([
            'success' => true,
            'message' => 'Project updated successfully!'
        ]);
    }

    if ($action === 'delete') {
        $id = intval($input['id'] ?? 0);
        if (!$id) {
            jsonResponse(['success' => false, 'error' => 'Missing project ID'], 400);
        }

        $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
        $stmt->execute([$id]);

        jsonResponse([
            'success' => true,
            'message' => 'Project deleted successfully!'
        ]);
    }

    if ($action === 'reorder') {
        $orderList = $input['order'] ?? []; // Array of {id, sort_order}
        if (!is_array($orderList)) {
            jsonResponse(['success' => false, 'error' => 'Invalid order data'], 400);
        }

        $stmt = $pdo->prepare("UPDATE projects SET sort_order = ? WHERE id = ?");
        foreach ($orderList as $item) {
            $stmt->execute([intval($item['sort_order']), intval($item['id'])]);
        }

        jsonResponse(['success' => true, 'message' => 'Projects reordered successfully!']);
    }
}

jsonResponse(['error' => 'Unsupported method or action'], 400);
