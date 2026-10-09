<?php
/**
 * Site Settings API
 * Public GET for portfolio dynamic text; Authenticated POST for admin.
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth_helper.php';

header('Content-Type: application/json; charset=utf-8');

$pdo = getDbConnection();
$method = $_SERVER['REQUEST_METHOD'];

// GET: Return all settings
if ($method === 'GET') {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
    $rows = $stmt->fetchAll();
    $settings = [];
    foreach ($rows as $r) {
        $settings[$r['setting_key']] = $r['setting_value'];
    }
    jsonResponse(['success' => true, 'settings' => $settings]);
}

// POST: Update settings (Admin only)
requireAdminAuth();

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $settings = $input['settings'] ?? $input;

    if (!is_array($settings)) {
        jsonResponse(['success' => false, 'error' => 'Invalid settings payload'], 400);
    }

    $stmt = $pdo->prepare("
        INSERT INTO site_settings (setting_key, setting_value) 
        VALUES (?, ?) 
        ON CONFLICT(setting_key) DO UPDATE SET setting_value = excluded.setting_value
    ");

    // For MySQL compatibility if active:
    if (($GLOBALS['ACTIVE_DB_TYPE'] ?? '') === 'mysql') {
        $stmt = $pdo->prepare("
            INSERT INTO site_settings (setting_key, setting_value) 
            VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ");
    }

    foreach ($settings as $key => $val) {
        if ($key === 'action') continue;
        $stmt->execute([$key, strval($val)]);
    }

    jsonResponse(['success' => true, 'message' => 'Settings updated successfully!']);
}

jsonResponse(['error' => 'Method not allowed'], 405);
