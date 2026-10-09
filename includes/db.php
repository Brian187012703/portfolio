<?php
/**
 * Database Connection & Migration Layer
 * Supports MySQL (Production / HostForge) & SQLite (Zero-Config / Local)
 */

require_once __DIR__ . '/../config.php';

function getDbConnection() {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $driver = DB_DRIVER;

    // Try MySQL first if driver is mysql or auto
    if ($driver === 'mysql' || $driver === 'auto') {
        try {
            $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT, DB_NAME);
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_TIMEOUT => 2,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            $GLOBALS['ACTIVE_DB_TYPE'] = 'mysql';
        } catch (PDOException $e) {
            if ($driver === 'mysql') {
                // If explicitly requested mysql and failed, throw exception
                throw new Exception("MySQL Connection failed: " . $e->getMessage());
            }
            // Auto mode: Fall back to SQLite
            $pdo = null;
        }
    }

    // Fall back to SQLite if MySQL was not established
    if ($pdo === null) {
        $dataDir = dirname(SQLITE_FILE);
        if (!is_dir($dataDir)) {
            mkdir($dataDir, 0755, true);
        }

        $dsn = 'sqlite:' . SQLITE_FILE;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ];
        $pdo = new PDO($dsn, null, null, $options);
        // Enable WAL mode for better concurrency in SQLite
        $pdo->exec('PRAGMA journal_mode = WAL;');
        $GLOBALS['ACTIVE_DB_TYPE'] = 'sqlite';
    }

    // Auto-initialize schema if needed
    initializeDatabase($pdo, $GLOBALS['ACTIVE_DB_TYPE']);

    return $pdo;
}

function initializeDatabase(PDO $pdo, string $type) {
    // Check if tables already exist
    $checkSql = ($type === 'mysql')
        ? "SHOW TABLES LIKE 'admin_users'"
        : "SELECT name FROM sqlite_master WHERE type='table' AND name='admin_users'";

    $stmt = $pdo->query($checkSql);
    if ($stmt->fetch()) {
        return; // Already initialized
    }

    // Create Tables
    if ($type === 'mysql') {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS admin_users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(100) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                email VARCHAR(150) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

            CREATE TABLE IF NOT EXISTS projects (
                id INT AUTO_INCREMENT PRIMARY KEY,
                slug VARCHAR(100) NOT NULL,
                title VARCHAR(200) NOT NULL,
                category VARCHAR(50) NOT NULL,
                category_label VARCHAR(100) DEFAULT '',
                client VARCHAR(150) DEFAULT '',
                year VARCHAR(50) DEFAULT '',
                role VARCHAR(150) DEFAULT '',
                deliverables TEXT,
                image VARCHAR(255) NOT NULL,
                description TEXT,
                highlights TEXT,
                live_demo_url VARCHAR(255) DEFAULT '#',
                sort_order INT DEFAULT 0,
                is_featured INT DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

            CREATE TABLE IF NOT EXISTS messages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(150) NOT NULL,
                email VARCHAR(150) NOT NULL,
                artwork_service VARCHAR(150) DEFAULT '',
                project_budget VARCHAR(100) DEFAULT '',
                message TEXT NOT NULL,
                status VARCHAR(30) DEFAULT 'unread',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

            CREATE TABLE IF NOT EXISTS site_settings (
                setting_key VARCHAR(100) PRIMARY KEY,
                setting_value TEXT,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

            CREATE TABLE IF NOT EXISTS skills (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                proficiency VARCHAR(50) DEFAULT 'Advanced',
                category VARCHAR(50) DEFAULT 'general',
                sort_order INT DEFAULT 0
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    } else {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS admin_users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT NOT NULL UNIQUE,
                password_hash TEXT NOT NULL,
                email TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS projects (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                slug TEXT NOT NULL,
                title TEXT NOT NULL,
                category TEXT NOT NULL,
                category_label TEXT DEFAULT '',
                client TEXT DEFAULT '',
                year TEXT DEFAULT '',
                role TEXT DEFAULT '',
                deliverables TEXT DEFAULT '',
                image TEXT NOT NULL,
                description TEXT DEFAULT '',
                highlights TEXT DEFAULT '',
                live_demo_url TEXT DEFAULT '#',
                sort_order INTEGER DEFAULT 0,
                is_featured INTEGER DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS messages (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT NOT NULL,
                artwork_service TEXT DEFAULT '',
                project_budget TEXT DEFAULT '',
                message TEXT NOT NULL,
                status TEXT DEFAULT 'unread',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS site_settings (
                setting_key TEXT PRIMARY KEY,
                setting_value TEXT,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS skills (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                proficiency TEXT DEFAULT 'Advanced',
                category TEXT DEFAULT 'general',
                sort_order INTEGER DEFAULT 0
            );
        ");
    }

    // Seed Admin User
    $adminPassHash = password_hash(DEFAULT_ADMIN_PASS, PASSWORD_BCRYPT);
    $adminStmt = $pdo->prepare("INSERT INTO admin_users (username, password_hash, email) VALUES (?, ?, ?)");
    $adminStmt->execute([DEFAULT_ADMIN_USER, $adminPassHash, DEFAULT_ADMIN_EMAIL]);

    // Seed Initial Projects
    $seedProjects = [
        [
            'slug' => 'social-media',
            'title' => 'SOCIAL MEDIA POSTS',
            'category' => 'social',
            'category_label' => 'Branding & Creative Content',
            'client' => 'Brands & Digital Creators',
            'year' => '2023 — 2026',
            'role' => 'Graphic Artist & Content Stylist',
            'deliverables' => 'Social Media Banners, Story Creatives, Promo Ads, Visual Guides',
            'image' => 'assets/images/project_veloce.jpg',
            'description' => 'A collection of high-converting, attention-grabbing social media graphics crafted for viral reach and modern brand aesthetics. Built with high-contrast typography, color grading, and dynamic visual layouts across Instagram, Facebook, and promotional campaigns.',
            'highlights' => json_encode([
                'Engineered bold visual hierarchy tailored for scroll-stopping engagement on modern feeds.',
                'Utilized Photoshop, CorelDRAW, and Canva for versatile, fast-turnaround campaign packages.',
                'Created unified visual identity systems for multi-platform marketing launches.'
            ]),
            'live_demo_url' => '#',
            'sort_order' => 1,
            'is_featured' => 1
        ],
        [
            'slug' => 'custom-apparel',
            'title' => 'CUSTOM APPAREL',
            'category' => 'apparel',
            'category_label' => 'Clothing & Streetwear Merch',
            'client' => 'Apparel Brands & Independent Merch',
            'year' => '2022 — 2026',
            'role' => 'Apparel Graphic Artist',
            'deliverables' => 'Vector T-Shirt Graphics, Streetwear Typography, Silk Screen Separations',
            'image' => 'assets/images/project_woodcraft.jpg',
            'description' => 'Custom graphic designs engineered specifically for apparel production, streetwear brands, and event merchandise. Focused on intricate line art, typography composition, and color-separated vector files ready for screen printing and direct-to-garment (DTG) execution.',
            'highlights' => json_encode([
                'Mastery in vector separation, print specifications, and garment mockups.',
                'Delivered over 100+ unique shirt and hoodie designs for streetwear brands and client collections.',
                'Blends hand-drawn traditional character art with futuristic cyber-grunge typography.'
            ]),
            'live_demo_url' => '#',
            'sort_order' => 2,
            'is_featured' => 1
        ],
        [
            'slug' => 'digital-arts',
            'title' => 'DIGITAL ARTS',
            'category' => 'digital',
            'category_label' => 'Concept Art & Digital Illustration',
            'client' => 'Commissions & Creative Studios',
            'year' => '2021 — 2026',
            'role' => 'Digital Illustrator & Concept Artist',
            'deliverables' => 'High-Res Digital Paintings, Character Designs, Concept Artworks',
            'image' => 'assets/images/project_urbanic.jpg',
            'description' => 'Expansive digital art creations combining painterly brushwork with contemporary sci-fi and illustrative character art. Each piece balances dramatic rim lighting, anatomy precision, and atmospheric world-building.',
            'highlights' => json_encode([
                'Created utilizing Adobe Photoshop, graphic tablets, and AI-assisted workflows.',
                'Rich cinematic color palettes with high-depth contrast and rendering.',
                'Versatile styles ranging from anime/manga to hyper-stylized digital realism.'
            ]),
            'live_demo_url' => '#',
            'sort_order' => 3,
            'is_featured' => 1
        ],
        [
            'slug' => 'traditional-arts',
            'title' => 'TRADITIONAL ARTS',
            'category' => 'traditional',
            'category_label' => 'Drawing & Painting Studies',
            'client' => 'Private Art Collectors & Exhibitions',
            'year' => '2019 — 2026',
            'role' => 'Traditional Fine Artist',
            'deliverables' => 'Graphite Drawings, Ink Sketches, Canvas Paintings, Mixed Media',
            'image' => 'assets/images/project_neural.jpg',
            'description' => 'Foundational traditional artworks rooted in classic drawing techniques, portraiture, ink hatching, and vibrant painting mediums. Demonstrates deep understanding of anatomy, lighting, values, and organic textures that elevate digital work.',
            'highlights' => json_encode([
                'Specialized in charcoal, graphite realism, acrylic, and watercolor media.',
                'Over 5+ years of dedicated traditional sketchbooks and portfolio studies.',
                'Forms the irreplaceable organic foundation behind all digital and apparel works.'
            ]),
            'live_demo_url' => '#',
            'sort_order' => 4,
            'is_featured' => 1
        ]
    ];

    $projStmt = $pdo->prepare("
        INSERT INTO projects (slug, title, category, category_label, client, year, role, deliverables, image, description, highlights, live_demo_url, sort_order, is_featured)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    foreach ($seedProjects as $p) {
        $projStmt->execute([
            $p['slug'], $p['title'], $p['category'], $p['category_label'], $p['client'],
            $p['year'], $p['role'], $p['deliverables'], $p['image'], $p['description'],
            $p['highlights'], $p['live_demo_url'], $p['sort_order'], $p['is_featured']
        ]);
    }

    // Seed Site Settings
    $settings = [
        'hero_name' => 'BRIAN JOSHUA TANAEL',
        'hero_role' => 'Traditional & Graphic Artist',
        'hero_bio' => "I've worked on high-impact social media posts, custom apparel, digital arts, and traditional fine arts projects. Dedicated to bringing bold visual concepts to life through versatile traditional mediums and cutting-edge digital craftsmanship.",
        'hero_location' => 'Quezon City, Philippines',
        'availability_status' => 'Open for Projects & Freelance',
        'stat_years' => '5+',
        'stat_projects' => '1000+',
        'stat_clients' => '100+',
        'quote_sparkle' => 'Transforming creative ideas into powerful visual expressions and timeless artwork.',
        'contact_email' => 'Briantanael187@gmail.com',
        'contact_phone' => '+63 906 507 0059',
        'social_facebook' => 'https://www.facebook.com/anshalene.tanael/',
        'social_instagram' => 'https://www.instagram.com/wsy_qt/',
        'social_github' => 'https://github.com/Brian187012703'
    ];

    $setStmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)");
    foreach ($settings as $k => $v) {
        $setStmt->execute([$k, $v]);
    }

    // Seed Skills
    $seedSkills = [
        ['Photoshop', 'Expert'],
        ['CorelDRAW', 'Advanced'],
        ['Figma', 'Advanced'],
        ['Canva', 'Expert'],
        ['Traditional Drawing', 'Master'],
        ['Painting', 'Master'],
        ['AI Artistry', 'Advanced'],
        ['Adobe Premiere', 'Advanced'],
        ['PHP', 'Proficient']
    ];

    $skillStmt = $pdo->prepare("INSERT INTO skills (name, proficiency, sort_order) VALUES (?, ?, ?)");
    $sort = 1;
    foreach ($seedSkills as $s) {
        $skillStmt->execute([$s[0], $s[1], $sort++]);
    }
}
