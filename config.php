<?php
/**
 * Global Configuration for Portfolio & Admin Panel
 * Compatible with XAMPP (Local) and HostForge (cPanel / Production)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Environment Detection
$isLocal = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1']) || php_sapi_name() === 'cli';

// Database Configuration
// On HostForge, replace with your cPanel MySQL database credentials if desired.
// Mode 'auto' attempts MySQL connection; if not reachable or not configured,
// it safely falls back to SQLite so the site and admin work immediately with zero configuration!
define('DB_DRIVER', 'auto'); // 'auto', 'mysql', or 'sqlite'

define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'portfolio_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// SQLite file path
define('SQLITE_FILE', __DIR__ . '/data/portfolio.db');

// Uploads Directory
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('UPLOAD_URL', 'uploads/');

// Default Admin Credentials (auto-seeded into database on first run)
define('DEFAULT_ADMIN_USER', 'admin');
define('DEFAULT_ADMIN_PASS', 'admin123');
define('DEFAULT_ADMIN_EMAIL', 'Briantanael187@gmail.com');

// Site metadata
define('SITE_NAME', 'Brian Joshua Tanael — Portfolio');
define('ADMIN_TITLE', 'Brian Joshua Portfolio — Admin Control');
