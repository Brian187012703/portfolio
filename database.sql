-- Brian Joshua Tanael Portfolio Database Schema for HostForge / MySQL
-- HostForge cPanel phpMyAdmin Import File

CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `projects` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `slug` VARCHAR(100) NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `category` VARCHAR(50) NOT NULL,
  `category_label` VARCHAR(100) DEFAULT '',
  `client` VARCHAR(150) DEFAULT '',
  `year` VARCHAR(50) DEFAULT '',
  `role` VARCHAR(150) DEFAULT '',
  `deliverables` TEXT,
  `image` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `highlights` TEXT,
  `live_demo_url` VARCHAR(255) DEFAULT '#',
  `sort_order` INT DEFAULT 0,
  `is_featured` INT DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `artwork_service` VARCHAR(150) DEFAULT '',
  `project_budget` VARCHAR(100) DEFAULT '',
  `message` TEXT NOT NULL,
  `status` VARCHAR(30) DEFAULT 'unread',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `site_settings` (
  `setting_key` VARCHAR(100) PRIMARY KEY,
  `setting_value` TEXT,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `skills` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `proficiency` VARCHAR(50) DEFAULT 'Advanced',
  `category` VARCHAR(50) DEFAULT 'general',
  `sort_order` INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default Admin User (username: admin, password: admin123)
INSERT INTO `admin_users` (`username`, `password_hash`, `email`) 
VALUES ('admin', '$2y$10$wK10oP09aP6m0vD60k4hzeZzfvXFz2v5XnN085O19CslYc19bS3Wy', 'Briantanael187@gmail.com')
ON DUPLICATE KEY UPDATE `username`=`username`;

-- Default Projects
INSERT INTO `projects` (`slug`, `title`, `category`, `category_label`, `client`, `year`, `role`, `deliverables`, `image`, `description`, `highlights`, `live_demo_url`, `sort_order`, `is_featured`) VALUES
('social-media', 'SOCIAL MEDIA POSTS', 'social', 'Branding & Creative Content', 'Brands & Digital Creators', '2023 — 2026', 'Graphic Artist & Content Stylist', 'Social Media Banners, Story Creatives, Promo Ads, Visual Guides', 'assets/images/project_veloce.jpg', 'A collection of high-converting, attention-grabbing social media graphics crafted for viral reach and modern brand aesthetics. Built with high-contrast typography, color grading, and dynamic visual layouts across Instagram, Facebook, and promotional campaigns.', '["Engineered bold visual hierarchy tailored for scroll-stopping engagement on modern feeds.","Utilized Photoshop, CorelDRAW, and Canva for versatile, fast-turnaround campaign packages.","Created unified visual identity systems for multi-platform marketing launches."]', '#', 1, 1),
('custom-apparel', 'CUSTOM APPAREL', 'apparel', 'Clothing & Streetwear Merch', 'Apparel Brands & Independent Merch', '2022 — 2026', 'Apparel Graphic Artist', 'Vector T-Shirt Graphics, Streetwear Typography, Silk Screen Separations', 'assets/images/project_woodcraft.jpg', 'Custom graphic designs engineered specifically for apparel production, streetwear brands, and event merchandise. Focused on intricate line art, typography composition, and color-separated vector files ready for screen printing and direct-to-garment (DTG) execution.', '["Mastery in vector separation, print specifications, and garment mockups.","Delivered over 100+ unique shirt and hoodie designs for streetwear brands and client collections.","Blends hand-drawn traditional character art with futuristic cyber-grunge typography."]', '#', 2, 1),
('digital-arts', 'DIGITAL ARTS', 'digital', 'Concept Art & Digital Illustration', 'Commissions & Creative Studios', '2021 — 2026', 'Digital Illustrator & Concept Artist', 'High-Res Digital Paintings, Character Designs, Concept Artworks', 'assets/images/project_urbanic.jpg', 'Expansive digital art creations combining painterly brushwork with contemporary sci-fi and illustrative character art. Each piece balances dramatic rim lighting, anatomy precision, and atmospheric world-building.', '["Created utilizing Adobe Photoshop, graphic tablets, and AI-assisted workflows.","Rich cinematic color palettes with high-depth contrast and rendering.","Versatile styles ranging from anime/manga to hyper-stylized digital realism."]', '#', 3, 1),
('traditional-arts', 'TRADITIONAL ARTS', 'traditional', 'Drawing & Painting Studies', 'Private Art Collectors & Exhibitions', '2019 — 2026', 'Traditional Fine Artist', 'Graphite Drawings, Ink Sketches, Canvas Paintings, Mixed Media', 'assets/images/project_neural.jpg', 'Foundational traditional artworks rooted in classic drawing techniques, portraiture, ink hatching, and vibrant painting mediums. Demonstrates deep understanding of anatomy, lighting, values, and organic textures that elevate digital work.', '["Specialized in charcoal, graphite realism, acrylic, and watercolor media.","Over 5+ years of dedicated traditional sketchbooks and portfolio studies.","Forms the irreplaceable organic foundation behind all digital and apparel works."]', '#', 4, 1);

-- Default Site Settings
INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES
('hero_name', 'BRIAN JOSHUA TANAEL'),
('hero_role', 'Traditional & Graphic Artist'),
('hero_bio', 'I\\\'ve worked on high-impact social media posts, custom apparel, digital arts, and traditional fine arts projects. Dedicated to bringing bold visual concepts to life through versatile traditional mediums and cutting-edge digital craftsmanship.'),
('hero_location', 'Quezon City, Philippines'),
('availability_status', 'Open for Projects & Freelance'),
('stat_years', '5+'),
('stat_projects', '1000+'),
('stat_clients', '100+'),
('quote_sparkle', 'Transforming creative ideas into powerful visual expressions and timeless artwork.'),
('contact_email', 'Briantanael187@gmail.com'),
('contact_phone', '+63 906 507 0059'),
('social_facebook', 'https://www.facebook.com/anshalene.tanael/'),
('social_instagram', 'https://www.instagram.com/wsy_qt/'),
('social_github', 'https://github.com/Brian187012703')
ON DUPLICATE KEY UPDATE `setting_value`=VALUES(`setting_value`);

-- Default Skills
INSERT INTO `skills` (`name`, `proficiency`, `sort_order`) VALUES
('Photoshop', 'Expert', 1),
('CorelDRAW', 'Advanced', 2),
('Figma', 'Advanced', 3),
('Canva', 'Expert', 4),
('Traditional Drawing', 'Master', 5),
('Painting', 'Master', 6),
('AI Artistry', 'Advanced', 7),
('Adobe Premiere', 'Advanced', 8),
('PHP', 'Proficient', 9);
