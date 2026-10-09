<?php
/**
 * Authentication & Security Helper
 */

require_once __DIR__ . '/../config.php';

function isAdminLoggedIn(): bool {
    return !empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function requireAdminAuth() {
    if (!isAdminLoggedIn()) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Unauthorized: Please log in to the admin panel.'
        ]);
        exit;
    }
}

function jsonResponse(array $data, int $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}
