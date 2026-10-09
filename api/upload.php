<?php
/**
 * Image Upload API
 * Admin only. Secure image upload for artwork and portfolio assets.
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth_helper.php';

header('Content-Type: application/json; charset=utf-8');

requireAdminAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Only POST method is allowed'], 405);
}

if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    $errMap = [
        UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds upload_max_filesize.',
        UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds MAX_FILE_SIZE.',
        UPLOAD_ERR_PARTIAL    => 'The uploaded file was only partially uploaded.',
        UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload.'
    ];
    $code = $_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE;
    jsonResponse(['success' => false, 'error' => $errMap[$code] ?? 'File upload error.'], 400);
}

$file = $_FILES['image'];
$maxSize = 15 * 1024 * 1024; // 15 MB

if ($file['size'] > $maxSize) {
    jsonResponse(['success' => false, 'error' => 'Image size must be under 15MB.'], 400);
}

// Check real MIME type using finfo
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($file['tmp_name']);

$allowedMimes = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
    'image/gif'  => 'gif'
];

if (!isset($allowedMimes[$mime])) {
    jsonResponse(['success' => false, 'error' => 'Invalid image format. Allowed: JPG, PNG, WEBP, GIF.'], 400);
}

$ext = $allowedMimes[$mime];
$targetDir = __DIR__ . '/../uploads/projects/';

if (!is_dir($targetDir)) {
    mkdir($targetDir, 0755, true);
    // Add protective .htaccess
    file_put_contents(__DIR__ . '/../uploads/.htaccess', "Options -Indexes\n<FilesMatch \"\.(php|php5|php7|phtml|phar)$\">\nOrder Deny,Allow\nDeny from all\n</FilesMatch>\n");
}

$filename = 'art_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
$targetPath = $targetDir . $filename;

if (move_uploaded_file($file['tmp_name'], $targetPath)) {
    $relativeUrl = 'uploads/projects/' . $filename;
    jsonResponse([
        'success' => true,
        'message' => 'Image uploaded successfully!',
        'url' => $relativeUrl
    ]);
} else {
    jsonResponse(['success' => false, 'error' => 'Failed to save uploaded image.'], 500);
}
