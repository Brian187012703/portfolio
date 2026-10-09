<?php
/**
 * Main Admin Dashboard Application
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth_helper.php';

if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

$pdo = getDbConnection();
$activeDb = $GLOBALS['ACTIVE_DB_TYPE'] ?? 'sqlite';
$adminUser = $_SESSION['admin_user'] ?? 'Admin';
$adminEmail = $_SESSION['admin_email'] ?? 'Briantanael187@gmail.com';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard — Brian Joshua Portfolio</title>
  <link rel="stylesheet" href="../css/style.css?v=2.4">
  <link rel="stylesheet" href="css/admin.css?v=1.0">
  <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>⚡</text></svg>">
</head>
<body class="admin-body">

  <div class="admin-bg-glow"></div>
  <div class="noise-grid"></div>

  <!-- Sidebar -->
  <aside class="admin-sidebar" id="admin-sidebar">
    <div class="admin-sidebar-header">
      <div class="admin-brand">
        <span class="admin-brand-title">BRIAN JOSHUA</span>
        <span class="admin-brand-subtitle">ADMIN CONTROL // v2.0</span>
      </div>
      <button class="mobile-menu-btn" id="sidebar-close-btn" style="display: none;">✕</button>
    </div>

    <nav class="admin-nav">
      <a class="nav-link-item active" data-tab="overview">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="3" width="7" height="7"></rect>
          <rect x="14" y="3" width="7" height="7"></rect>
          <rect x="14" y="14" width="7" height="7"></rect>
          <rect x="3" y="14" width="7" height="7"></rect>
        </svg>
        <span>Overview</span>
      </a>

      <a class="nav-link-item" data-tab="projects">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
          <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
        </svg>
        <span>Selected Works</span>
        <span class="nav-badge" id="badge-projects-count">0</span>
      </a>

      <a class="nav-link-item" data-tab="messages">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
          <polyline points="22,6 12,13 2,6"></polyline>
        </svg>
        <span>Client Inquiries</span>
        <span class="nav-badge" id="badge-messages-unread">0</span>
      </a>

      <a class="nav-link-item" data-tab="settings">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="3"></circle>
          <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
        </svg>
        <span>Hero & Profile Bio</span>
      </a>

      <a class="nav-link-item" data-tab="skills">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
          <polyline points="2 17 12 22 22 17"></polyline>
          <polyline points="2 12 12 17 22 12"></polyline>
        </svg>
        <span>Skills & Toolset</span>
      </a>

      <a class="nav-link-item" data-tab="hostforge">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="2" y="2" width="20" height="8" rx="2" ry="2"></rect>
          <rect x="2" y="14" width="20" height="8" rx="2" ry="2"></rect>
          <line x1="6" y1="6" x2="6.01" y2="6"></line>
          <line x1="6" y1="18" x2="6.01" y2="18"></line>
        </svg>
        <span>HostForge Deploy</span>
      </a>

      <a class="nav-link-item" data-tab="account">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
        </svg>
        <span>Account & Security</span>
      </a>
    </nav>

    <div class="admin-sidebar-footer">
      <div class="admin-user-info">
        <div class="admin-user-avatar"><?= strtoupper(substr($adminUser, 0, 1)) ?></div>
        <div>
          <div class="admin-user-name"><?= htmlspecialchars($adminUser) ?></div>
          <div class="admin-user-role">Administrator</div>
        </div>
      </div>
      <button class="logout-btn" id="sidebar-logout-btn" title="Sign Out">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
          <polyline points="16 17 21 12 16 7"></polyline>
          <line x1="21" y1="12" x2="9" y2="12"></line>
        </svg>
      </button>
    </div>
  </aside>

  <!-- Main Container -->
  <main class="admin-main">
    <!-- Topbar -->
    <header class="admin-topbar">
      <div class="topbar-left">
        <button class="mobile-menu-btn" id="mobile-toggle-btn">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="3" y1="12" x2="21" y2="12"></line>
            <line x1="3" y1="6" x2="21" y2="6"></line>
            <line x1="3" y1="18" x2="21" y2="18"></line>
          </svg>
        </button>
        <div class="page-breadcrumb">
          ADMIN / <span class="current-page" id="breadcrumb-page-name">OVERVIEW</span>
        </div>
      </div>

      <div class="topbar-right">
        <div class="db-status-pill" title="Current Active Database Engine">
          <span class="status-dot-active"></span>
          <span>DB: <?= strtoupper($activeDb) ?> <?= ($activeDb === 'mysql') ? '(HostForge Live)' : '(Zero-Config Active)' ?></span>
        </div>

        <a href="../index.html" target="_blank" class="view-site-btn">
          <span>View Public Site</span>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
            <polyline points="15 3 21 3 21 9"></polyline>
            <line x1="10" y1="14" x2="21" y2="3"></line>
          </svg>
        </a>
      </div>
    </header>

    <!-- Dynamic Content Area -->
    <div class="admin-content">

      <!-- ==========================================
           TAB 1: OVERVIEW
           ========================================== -->
      <section class="admin-tab-view active" id="tab-overview">
        <div class="view-header">
          <div class="view-title-wrap">
            <h2>Command Center</h2>
            <p>Real-time analytics and management for Brian Joshua's portfolio.</p>
          </div>
          <div>
            <button class="btn-primary" id="btn-quick-add-project">
              <span>+ Add New Artwork</span>
            </button>
          </div>
        </div>

        <!-- Metrics Cards -->
        <div class="metrics-grid">
          <div class="metric-card">
            <div class="metric-icon">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                <polyline points="21 15 16 10 5 21"></polyline>
              </svg>
            </div>
            <div class="metric-info">
              <span class="metric-value" id="metric-projects-count">0</span>
              <span class="metric-label">Total Selected Works</span>
            </div>
          </div>

          <div class="metric-card">
            <div class="metric-icon" style="background: rgba(255, 30, 45, 0.15);">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                <polyline points="22,6 12,13 2,6"></polyline>
              </svg>
            </div>
            <div class="metric-info">
              <span class="metric-value" id="metric-unread-count">0</span>
              <span class="metric-label">Unread Inquiries</span>
            </div>
          </div>

          <div class="metric-card">
            <div class="metric-icon" style="background: rgba(0, 230, 118, 0.1); color: var(--admin-success); border-color: rgba(0, 230, 118, 0.3);">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
              </svg>
            </div>
            <div class="metric-info">
              <span class="metric-value" id="metric-skills-count">9</span>
              <span class="metric-label">Active Skills / Tools</span>
            </div>
          </div>

          <div class="metric-card">
            <div class="metric-icon" style="background: rgba(0, 176, 255, 0.1); color: var(--admin-info); border-color: rgba(0, 176, 255, 0.3);">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
              </svg>
            </div>
            <div class="metric-info">
              <span class="metric-value" style="font-size: 1.4rem;">ONLINE</span>
              <span class="metric-label">HostForge Compatibility</span>
            </div>
          </div>
        </div>

        <!-- Overview Sections Grid -->
        <div class="overview-sections-grid">
          <!-- Recent Inquiries -->
          <div class="admin-card">
            <div class="admin-card-header">
              <h3 class="admin-card-title">
                <span>Recent Commission Inquiries</span>
              </h3>
              <button class="btn-icon" onclick="switchTab('messages')">View All →</button>
            </div>
            <div class="admin-card-body" style="padding: 0;">
              <div class="table-responsive">
                <table class="admin-table" id="overview-inquiries-table">
                  <thead>
                    <tr>
                      <th>Client</th>
                      <th>Service Requested</th>
                      <th>Budget Scope</th>
                      <th>Status</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr><td colspan="5" style="text-align: center; padding: 24px;">Loading inquiries...</td></tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Quick Actions & Status -->
          <div class="admin-card">
            <div class="admin-card-header">
              <h3 class="admin-card-title">HostForge Deployment Status</h3>
            </div>
            <div class="admin-card-body">
              <div style="font-size: 0.85rem; color: var(--admin-text-secondary); line-height: 1.6; margin-bottom: 16px;">
                Your portfolio backend is ready for deployment to HostForge with dual-mode database support (MySQL & SQLite).
              </div>
              <ul style="padding-left: 20px; font-size: 0.82rem; color: var(--admin-text-muted); line-height: 1.8; margin-bottom: 20px;">
                <li>✓ API Endpoints active</li>
                <li>✓ Upload directory configured</li>
                <li>✓ Form submission saving to DB</li>
                <li>✓ MySQL schema ready in <code>database.sql</code></li>
              </ul>
              <button class="btn-primary" style="width: 100%; justify-content: center;" onclick="switchTab('hostforge')">
                <span>View HostForge Deploy Steps</span>
                <span style="font-size: 1.1rem;">→</span>
              </button>
            </div>
          </div>
        </div>
      </section>

      <!-- ==========================================
           TAB 2: SELECTED WORKS / PROJECTS
           ========================================== -->
      <section class="admin-tab-view" id="tab-projects">
        <div class="view-header">
          <div class="view-title-wrap">
            <h2>Selected Works Manager</h2>
            <p>Upload artwork pictures, define case studies, and organize portfolio projects.</p>
          </div>
          <div>
            <button class="btn-primary" id="btn-add-project">
              <span>+ Add New Artwork</span>
            </button>
          </div>
        </div>

        <div class="admin-card">
          <div class="admin-card-header" style="flex-wrap: wrap; gap: 12px;">
            <div class="project-filter-tabs" style="margin: 0;">
              <button class="filter-tab active" data-admin-filter="all">All Artworks</button>
              <button class="filter-tab" data-admin-filter="social">Social Media</button>
              <button class="filter-tab" data-admin-filter="apparel">Apparel</button>
              <button class="filter-tab" data-admin-filter="digital">Digital Arts</button>
              <button class="filter-tab" data-admin-filter="traditional">Traditional Arts</button>
            </div>
          </div>
          <div class="admin-card-body" style="padding: 0;">
            <div class="table-responsive">
              <table class="admin-table" id="projects-table">
                <thead>
                  <tr>
                    <th>Artwork Cover</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Client / Brand</th>
                    <th>Timeline</th>
                    <th>Order</th>
                    <th>Featured</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr><td colspan="8" style="text-align: center; padding: 24px;">Loading artworks...</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </section>

      <!-- ==========================================
           TAB 3: CLIENT INQUIRIES / INBOX
           ========================================== -->
      <section class="admin-tab-view" id="tab-messages">
        <div class="view-header">
          <div class="view-title-wrap">
            <h2>Commission Inquiries & Leads</h2>
            <p>Direct commission briefs submitted through the portfolio contact modal.</p>
          </div>
        </div>

        <div class="admin-card">
          <div class="admin-card-header" style="flex-wrap: wrap; gap: 12px;">
            <div class="project-filter-tabs" style="margin: 0;">
              <button class="filter-tab active" data-msg-filter="all">All Messages</button>
              <button class="filter-tab" data-msg-filter="unread">Unread Only</button>
              <button class="filter-tab" data-msg-filter="read">Read</button>
              <button class="filter-tab" data-msg-filter="archived">Archived</button>
            </div>
          </div>
          <div class="admin-card-body" style="padding: 0;">
            <div class="table-responsive">
              <table class="admin-table" id="messages-table">
                <thead>
                  <tr>
                    <th>Date</th>
                    <th>Client Name</th>
                    <th>Email Address</th>
                    <th>Artwork Service</th>
                    <th>Project Scope</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr><td colspan="7" style="text-align: center; padding: 24px;">Loading inquiries...</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </section>

      <!-- ==========================================
           TAB 4: HERO & PROFILE BIO SETTINGS
           ========================================== -->
      <section class="admin-tab-view" id="tab-settings">
        <div class="view-header">
          <div class="view-title-wrap">
            <h2>Hero & Profile Settings</h2>
            <p>Customize the headline, bio copy, contact information, metrics, and social profiles.</p>
          </div>
        </div>

        <form id="settings-form">
          <div class="admin-card">
            <div class="admin-card-header">
              <h3 class="admin-card-title">Artist Information & Hero Headline</h3>
            </div>
            <div class="admin-card-body">
              <div class="form-grid-2">
                <div class="form-group">
                  <label for="set-hero-name">Full Name</label>
                  <input type="text" id="set-hero-name" name="hero_name" class="form-control" value="BRIAN JOSHUA TANAEL" required>
                </div>
                <div class="form-group">
                  <label for="set-hero-role">Subrole / Title</label>
                  <input type="text" id="set-hero-role" name="hero_role" class="form-control" value="Traditional & Graphic Artist" required>
                </div>
              </div>

              <div class="form-group">
                <label for="set-hero-bio">Artist Bio Description</label>
                <textarea id="set-hero-bio" name="hero_bio" class="form-control" rows="3" required></textarea>
              </div>

              <div class="form-grid-2">
                <div class="form-group">
                  <label for="set-hero-location">Base Location</label>
                  <input type="text" id="set-hero-location" name="hero_location" class="form-control" value="Quezon City, Philippines">
                </div>
                <div class="form-group">
                  <label for="set-availability">Availability Status Badge</label>
                  <input type="text" id="set-availability" name="availability_status" class="form-control" value="Open for Projects & Freelance">
                </div>
              </div>

              <div class="form-group">
                <label for="set-quote-sparkle">Sparkle Quote</label>
                <input type="text" id="set-quote-sparkle" name="quote_sparkle" class="form-control" value="Transforming creative ideas into powerful visual expressions and timeless artwork.">
              </div>
            </div>
          </div>

          <div class="admin-card">
            <div class="admin-card-header">
              <h3 class="admin-card-title">Metrics & Counters</h3>
            </div>
            <div class="admin-card-body">
              <div class="form-grid-3">
                <div class="form-group">
                  <label for="set-stat-years">Years Experience</label>
                  <input type="text" id="set-stat-years" name="stat_years" class="form-control" value="5+">
                </div>
                <div class="form-group">
                  <label for="set-stat-projects">Projects Completed</label>
                  <input type="text" id="set-stat-projects" name="stat_projects" class="form-control" value="1000+">
                </div>
                <div class="form-group">
                  <label for="set-stat-clients">Happy Clients</label>
                  <input type="text" id="set-stat-clients" name="stat_clients" class="form-control" value="100+">
                </div>
              </div>
            </div>
          </div>

          <div class="admin-card">
            <div class="admin-card-header">
              <h3 class="admin-card-title">Contact & Social Channels</h3>
            </div>
            <div class="admin-card-body">
              <div class="form-grid-2">
                <div class="form-group">
                  <label for="set-contact-email">Direct Email</label>
                  <input type="email" id="set-contact-email" name="contact_email" class="form-control" value="Briantanael187@gmail.com">
                </div>
                <div class="form-group">
                  <label for="set-contact-phone">Direct Phone / WhatsApp</label>
                  <input type="text" id="set-contact-phone" name="contact_phone" class="form-control" value="+63 906 507 0059">
                </div>
              </div>

              <div class="form-grid-3">
                <div class="form-group">
                  <label for="set-social-fb">Facebook URL</label>
                  <input type="url" id="set-social-fb" name="social_facebook" class="form-control" value="https://www.facebook.com/anshalene.tanael/">
                </div>
                <div class="form-group">
                  <label for="set-social-ig">Instagram URL</label>
                  <input type="url" id="set-social-ig" name="social_instagram" class="form-control" value="https://www.instagram.com/wsy_qt/">
                </div>
                <div class="form-group">
                  <label for="set-social-gh">GitHub URL</label>
                  <input type="url" id="set-social-gh" name="social_github" class="form-control" value="https://github.com/Brian187012703">
                </div>
              </div>

              <button type="submit" id="save-settings-btn" class="btn-primary" style="margin-top: 10px;">
                <span>Save All Profile Settings</span>
                <span style="font-size: 1.1rem;">✓</span>
              </button>
            </div>
          </div>
        </form>
      </section>

      <!-- ==========================================
           TAB 5: SKILLS & TOOLSET
           ========================================== -->
      <section class="admin-tab-view" id="tab-skills">
        <div class="view-header">
          <div class="view-title-wrap">
            <h2>Skills & Toolset Manager</h2>
            <p>Add, edit, or reorganize software proficiency and traditional fine arts skills.</p>
          </div>
        </div>

        <div class="overview-sections-grid">
          <div class="admin-card">
            <div class="admin-card-header">
              <h3 class="admin-card-title">Current Skills</h3>
            </div>
            <div class="admin-card-body">
              <div id="skills-list-container" style="display: flex; flex-wrap: wrap; gap: 10px;">
                <!-- Dynamically loaded skills pills -->
              </div>
            </div>
          </div>

          <div class="admin-card">
            <div class="admin-card-header">
              <h3 class="admin-card-title">+ Add New Skill</h3>
            </div>
            <div class="admin-card-body">
              <form id="add-skill-form">
                <div class="form-group">
                  <label for="skill-name">Skill / Software Name</label>
                  <input type="text" id="skill-name" class="form-control" placeholder="e.g. Blender, InDesign, Watercolor" required>
                </div>
                <div class="form-group">
                  <label for="skill-proficiency">Proficiency Level</label>
                  <select id="skill-proficiency" class="form-control">
                    <option value="Master">Master</option>
                    <option value="Expert" selected>Expert</option>
                    <option value="Advanced">Advanced</option>
                    <option value="Proficient">Proficient</option>
                  </select>
                </div>
                <button type="submit" class="btn-primary" style="width: 100%; justify-content: center;">
                  <span>Add Skill</span>
                </button>
              </form>
            </div>
          </div>
        </div>
      </section>

      <!-- ==========================================
           TAB 6: HOSTFORGE DEPLOYMENT GUIDE
           ========================================== -->
      <section class="admin-tab-view" id="tab-hostforge">
        <div class="view-header">
          <div class="view-title-wrap">
            <h2>HostForge Deployment Center</h2>
            <p>Step-by-step instructions for uploading your portfolio and admin panel to HostForge.</p>
          </div>
        </div>

        <div class="admin-card">
          <div class="admin-card-header">
            <h3 class="admin-card-title">HostForge Deployment Steps</h3>
          </div>
          <div class="admin-card-body">

            <div class="deploy-step-card">
              <div class="step-num">1</div>
              <div class="step-content">
                <h4>Prepare & Upload Files</h4>
                <p>In your HostForge cPanel, navigate to <strong>File Manager</strong> → <code>public_html</code> (or your domain directory). Upload all files from your portfolio folder, or zip them and extract in <code>public_html</code>.</p>
                <div class="code-box">public_html/
├── index.html / index.php
├── config.php
├── database.sql
├── includes/
├── api/
├── admin/
├── css/
├── js/
├── assets/
└── uploads/</div>
              </div>
            </div>

            <div class="deploy-step-card">
              <div class="step-num">2</div>
              <div class="step-content">
                <h4>Database Option A: Zero-Config SQLite (Instant Setup)</h4>
                <p>The system is configured with <code>DB_DRIVER = 'auto'</code>. If you do not configure MySQL, it automatically creates and runs on SQLite inside <code>data/portfolio.db</code>. Ensure the <code>data/</code> and <code>uploads/</code> folders have write permissions (755 or 777).</p>
              </div>
            </div>

            <div class="deploy-step-card">
              <div class="step-num">3</div>
              <div class="step-content">
                <h4>Database Option B: HostForge cPanel MySQL (Recommended for Production)</h4>
                <p>1. Go to HostForge cPanel → <strong>MySQL Databases</strong>.</p>
                <p>2. Create a new database (e.g. <code>username_portfolio</code>), create a user, and assign All Privileges.</p>
                <p>3. Go to <strong>phpMyAdmin</strong>, select your new database, and click <strong>Import</strong> → choose <code>database.sql</code>.</p>
                <p>4. Open <code>config.php</code> in HostForge File Manager and update credentials:</p>
                <div class="code-box">define('DB_DRIVER', 'mysql');
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_cpanel_db_name');
define('DB_USER', 'your_cpanel_db_user');
define('DB_PASS', 'your_db_password');</div>
              </div>
            </div>

            <div class="deploy-step-card">
              <div class="step-num">4</div>
              <div class="step-content">
                <h4>Access Admin Panel on Live Domain</h4>
                <p>Visit <code>https://yourdomain.com/admin/</code> to log in with your credentials.</p>
              </div>
            </div>

          </div>
        </div>
      </section>

      <!-- ==========================================
           TAB 7: ACCOUNT & SECURITY
           ========================================== -->
      <section class="admin-tab-view" id="tab-account">
        <div class="view-header">
          <div class="view-title-wrap">
            <h2>Account & Security</h2>
            <p>Update administrator credentials and security settings.</p>
          </div>
        </div>

        <div class="admin-card" style="max-width: 600px;">
          <div class="admin-card-header">
            <h3 class="admin-card-title">Update Administrator Profile</h3>
          </div>
          <div class="admin-card-body">
            <div id="account-alert" class="admin-alert" style="display: none;"></div>

            <form id="account-form">
              <div class="form-group">
                <label for="acc-username">Admin Username</label>
                <input type="text" id="acc-username" name="username" class="form-control" value="<?= htmlspecialchars($adminUser) ?>" required>
              </div>

              <div class="form-group">
                <label for="acc-email">Admin Email (Inquiry Notifications)</label>
                <input type="email" id="acc-email" name="email" class="form-control" value="<?= htmlspecialchars($adminEmail) ?>" required>
              </div>

              <hr style="border: none; border-top: 1px solid var(--admin-border); margin: 24px 0;">

              <h4 style="margin: 0 0 14px 0; color: #fff; font-size: 0.95rem;">Change Password (leave blank if keeping current)</h4>

              <div class="form-group">
                <label for="acc-current-pass">Current Password</label>
                <input type="password" id="acc-current-pass" name="current_password" class="form-control" placeholder="Enter current password">
              </div>

              <div class="form-group">
                <label for="acc-new-pass">New Password (minimum 6 characters)</label>
                <input type="password" id="acc-new-pass" name="new_password" class="form-control" placeholder="Enter new password">
              </div>

              <button type="submit" id="acc-submit-btn" class="btn-primary" style="margin-top: 10px;">
                <span>Update Credentials</span>
                <span style="font-size: 1.1rem;">✓</span>
              </button>
            </form>
          </div>
        </div>
      </section>

    </div>
  </main>

  <!-- ==========================================
       MODAL: ADD / EDIT PROJECT
       ========================================== -->
  <div class="admin-modal-overlay" id="project-modal-dialog">
    <div class="admin-modal-content">
      <div class="admin-modal-header">
        <h3 class="admin-modal-title" id="modal-project-heading">Add New Artwork</h3>
        <button class="admin-modal-close" onclick="closeProjectDialog()">&times;</button>
      </div>
      <div class="admin-modal-body">
        <form id="project-edit-form">
          <input type="hidden" id="proj-id" name="id" value="">
          <input type="hidden" id="proj-action" name="action" value="create">

          <div class="form-grid-2">
            <div class="form-group">
              <label for="proj-title">Artwork / Project Title *</label>
              <input type="text" id="proj-title" name="title" class="form-control" placeholder="e.g. VELOCE MOTORSPORT BRANDING" required>
            </div>
            <div class="form-group">
              <label for="proj-category">Category *</label>
              <select id="proj-category" name="category" class="form-control" required>
                <option value="social">Social Media</option>
                <option value="apparel">Custom Apparel</option>
                <option value="digital">Digital Arts</option>
                <option value="traditional">Traditional Arts</option>
              </select>
            </div>
          </div>

          <div class="form-grid-2">
            <div class="form-group">
              <label for="proj-cat-label">Category Subtitle Tag</label>
              <input type="text" id="proj-cat-label" name="category_label" class="form-control" placeholder="e.g. Branding & Creative Content">
            </div>
            <div class="form-group">
              <label for="proj-client">Client / Brand</label>
              <input type="text" id="proj-client" name="client" class="form-control" placeholder="e.g. Streetwear Label, Commission">
            </div>
          </div>

          <div class="form-grid-3">
            <div class="form-group">
              <label for="proj-year">Year / Timeline</label>
              <input type="text" id="proj-year" name="year" class="form-control" placeholder="e.g. 2024 — 2026">
            </div>
            <div class="form-group">
              <label for="proj-role">Role</label>
              <input type="text" id="proj-role" name="role" class="form-control" placeholder="e.g. Graphic Artist">
            </div>
            <div class="form-group">
              <label for="proj-order">Sort Order</label>
              <input type="number" id="proj-order" name="sort_order" class="form-control" value="0">
            </div>
          </div>

          <div class="form-group">
            <label for="proj-deliverables">Tools Used / Deliverables</label>
            <input type="text" id="proj-deliverables" name="deliverables" class="form-control" placeholder="e.g. Photoshop, CorelDRAW, High-Res Vector">
          </div>

          <!-- Image Upload & Preview -->
          <div class="form-group">
            <label>Artwork Image / Cover Picture *</label>
            <div class="image-upload-box" id="image-drop-area">
              <img id="image-preview" class="image-upload-preview" alt="Artwork Preview">
              <div id="image-upload-prompt">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--admin-accent); margin-bottom: 8px;">
                  <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                  <polyline points="17 8 12 3 7 8"></polyline>
                  <line x1="12" y1="3" x2="12" y2="15"></line>
                </svg>
                <div style="font-size: 0.88rem; font-weight: 600; color: #fff;">Click or Drag & Drop image here</div>
                <div style="font-size: 0.75rem; color: var(--admin-text-muted); margin-top: 4px;">JPG, PNG, WEBP up to 15MB</div>
              </div>
              <input type="file" id="proj-image-file" class="image-upload-input" accept="image/jpeg,image/png,image/webp,image/gif">
            </div>
            <input type="hidden" id="proj-image-url" name="image" value="">
          </div>

          <div class="form-group">
            <label for="proj-desc">Case Study Description</label>
            <textarea id="proj-desc" name="description" class="form-control" rows="3" placeholder="Describe the creative background, artistic choices, and technique..."></textarea>
          </div>

          <div class="form-group">
            <label for="proj-highlights">Key Deliverables & Highlights (one per line)</label>
            <textarea id="proj-highlights" name="highlights" class="form-control" rows="3" placeholder="Engineered bold visual hierarchy...&#10;Silk-screen ready separations...&#10;Custom typography..."></textarea>
          </div>

          <div class="form-grid-2">
            <div class="form-group">
              <label for="proj-link">Live Demo / Commission Link</label>
              <input type="text" id="proj-link" name="live_demo_url" class="form-control" value="#">
            </div>
            <div class="form-group">
              <label for="proj-featured">Featured on Portfolio</label>
              <select id="proj-featured" name="is_featured" class="form-control">
                <option value="1">Yes (Visible)</option>
                <option value="0">No (Hidden)</option>
              </select>
            </div>
          </div>
        </form>
      </div>
      <div class="admin-modal-footer">
        <button class="btn-icon" onclick="closeProjectDialog()">Cancel</button>
        <button class="btn-primary" id="save-project-btn">
          <span>Save Artwork</span>
          <span style="font-size: 1.1rem;">✓</span>
        </button>
      </div>
    </div>
  </div>

  <!-- ==========================================
       MODAL: VIEW INQUIRY DETAILS
       ========================================== -->
  <div class="admin-modal-overlay" id="inquiry-modal-dialog">
    <div class="admin-modal-content" style="max-width: 620px;">
      <div class="admin-modal-header">
        <h3 class="admin-modal-title">Client Inquiry Details</h3>
        <button class="admin-modal-close" onclick="closeInquiryDialog()">&times;</button>
      </div>
      <div class="admin-modal-body" id="inquiry-modal-body">
        <!-- Rendered dynamically -->
      </div>
      <div class="admin-modal-footer" id="inquiry-modal-footer">
        <!-- Action buttons -->
      </div>
    </div>
  </div>

  <!-- Toast Notification Container -->
  <div class="toast-container"></div>

  <!-- Admin Application Logic -->
  <script src="js/admin.js?v=1.0"></script>
</body>
</html>
