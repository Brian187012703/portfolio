# HostForge Deployment Guide — Brian Joshua Tanael Portfolio & Admin

This guide provides step-by-step instructions for deploying your portfolio and Admin Control Panel to **HostForge** (cPanel / Apache / PHP / MySQL).

---

## 🚀 Quick Overview

- **Admin Portal URL**: `https://yourdomain.com/admin/`
- **Default Admin Username**: `admin`
- **Default Admin Password**: `admin123`
- **Supported Databases**: Dual-mode — **MySQL** (cPanel standard) and **SQLite** (instant zero-config).

---

## 📁 Project File Structure

```text
public_html/
├── index.html            # Public portfolio homepage
├── index.php             # Apache default PHP index
├── config.php            # Database & site configuration
├── database.sql          # MySQL database schema for HostForge phpMyAdmin
├── admin/                # Admin Control Panel
│   ├── index.php         # Admin dashboard
│   ├── login.php         # Admin authentication
│   ├── css/admin.css     # Cyberpunk dark dashboard styling
│   └── js/admin.js       # Admin client reactive logic
├── api/                  # REST API endpoints
│   ├── auth.php          # Admin authentication & profile
│   ├── projects.php      # Project CRUD & reordering
│   ├── messages.php      # Client inquiries inbox
│   ├── settings.php      # Hero text & profile settings
│   ├── skills.php        # Skills & tools manager
│   └── upload.php        # Secure image file uploader
├── includes/
│   ├── db.php            # Dual MySQL/SQLite connection & auto-migration
│   └── auth_helper.php   # Security session validation
├── data/                 # SQLite database folder (auto-created if using SQLite)
│   └── portfolio.db
├── uploads/              # Uploaded project images & artworks
│   ├── .htaccess         # Script execution protection
│   └── projects/
├── css/
│   └── style.css
├── js/
│   └── main.js
└── assets/
    └── images/
```

---

## 📦 Step 1: Prepare & Upload Files to HostForge

1. Select all files and folders in your portfolio folder on your computer.
2. Compress them into a `.zip` archive (e.g. `portfolio.zip`).
3. Log in to your **HostForge Client Area** and open your **cPanel**.
4. In cPanel, click on **File Manager**.
5. Navigate to your website root directory:
   - For primary domain: `public_html/`
   - For addon domain or subdomain: `public_html/your-subdomain/`
6. Click **Upload** and upload `portfolio.zip`.
7. Once uploaded, right-click `portfolio.zip` in File Manager and select **Extract**.

---

## 🗄️ Step 2: Database Setup

You have two simple options:

### Option A: Zero-Config SQLite (Instant — No Database Setup Required!)
Your portfolio is equipped with an auto-fallback engine (`DB_DRIVER = 'auto'`).
- If you don't configure MySQL, it runs seamlessly on SQLite inside `data/portfolio.db`.
- **Permissions**: In File Manager, right-click `data/` and `uploads/`, select **Change Permissions**, and ensure they are set to `755` (or `775`).
- The system automatically creates the database, seeds default artworks, and you're done!

---

### Option B: HostForge cPanel MySQL (Recommended for Production)

If you prefer a standard MySQL database on HostForge:

1. **Create MySQL Database**:
   - In cPanel, navigate to **Databases** → **MySQL Databases**.
   - Under **Create New Database**, name it (e.g., `brian_portfolio`) and click **Create Database**.
   
2. **Create MySQL User**:
   - Under **MySQL Users** → **Add New User**, enter a username (e.g., `brian_user`) and a secure password.
   - Click **Create User**.

3. **Link User to Database**:
   - Under **Add User To Database**, select your new user and database.
   - Click **Add**.
   - Check **ALL PRIVILEGES** and click **Make Changes**.

4. **Import Database Schema**:
   - In cPanel, open **phpMyAdmin**.
   - In the left sidebar, click your new database (`..._portfolio`).
   - Click the **Import** tab at the top.
   - Click **Choose File** and select `database.sql` from your computer.
   - Scroll down and click **Import** (or **Go**). All tables and seed artworks will be created!

5. **Update `config.php`**:
   - In HostForge cPanel File Manager, right-click `config.php` and click **Edit**.
   - Update lines 18–25 with your cPanel MySQL details:

```php
define('DB_DRIVER', 'mysql');
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'cpanelusername_portfolio');
define('DB_USER', 'cpanelusername_user');
define('DB_PASS', 'YourStrongPasswordHere');
```

---

## 🔒 Step 3: Log In and Secure Your Admin Panel

1. Open your browser and visit:
   `https://yourdomain.com/admin/`
2. Log in with the default credentials:
   - **Username**: `admin`
   - **Password**: `admin123`
3. In the admin sidebar, click on **Account & Security**.
4. Change your **Username**, **Email**, and set a new secure **Password**.
5. Click **Update Credentials**.

---

## 🎨 Step 4: Manage Your Portfolio from the Admin Panel

- **Selected Works**: Click **Selected Works** to add new artworks, drag-and-drop cover photos, update categories (Social Media, Apparel, Digital Arts, Traditional Arts), and write case studies.
- **Client Inquiries**: When clients submit the contact form on your portfolio, inquiries instantly show up in the **Client Inquiries** inbox. You can open any inquiry and click **✉ Reply via Email** to respond directly!
- **Hero & Profile**: Edit your headline, biography, location, availability badge, and metrics (Years, Projects, Clients) anytime with one click.
- **Skills**: Add or remove software and fine art tools.

---

## 💡 Troubleshooting & FAQs

- **Images not uploading?**
  Ensure the `uploads/` directory exists and has write permissions (`755` or `775`).
- **PHP Version?**
  HostForge supports PHP 7.4 through PHP 8.3+. In cPanel, **Select PHP Version** → PHP 8.1 or 8.2 is recommended.
- **SSL / HTTPS?**
  HostForge includes free AutoSSL (Let's Encrypt / cPanel SSL). In cPanel, verify that SSL status is active for your domain.
