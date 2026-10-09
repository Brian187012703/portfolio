<?php
/**
 * Admin Login Page
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth_helper.php';

if (isAdminLoggedIn()) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login — Brian Joshua Portfolio</title>
  <link rel="stylesheet" href="../css/style.css?v=2.4">
  <link rel="stylesheet" href="css/admin.css?v=1.0">
  <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>⚡</text></svg>">
</head>
<body class="admin-login-body">

  <div class="noise-grid"></div>
  <div class="scanline-overlay"></div>

  <div class="login-wrapper">
    <div class="login-card">
      <div class="login-header">
        <div class="login-badge">CONTROL PANEL // SECURE ACCESS</div>
        <h1 class="login-title">ADMIN LOGIN</h1>
        <p class="login-subtitle">Authenticate to manage portfolio projects, client inquiries, and site content.</p>
      </div>

      <div id="login-alert" class="admin-alert" style="display: none;"></div>

      <form id="admin-login-form">
        <div class="form-group">
          <label for="username">Username</label>
          <div class="input-with-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
              <circle cx="12" cy="7" r="4"></circle>
            </svg>
            <input type="text" id="username" name="username" class="form-control" placeholder="Enter username" required autofocus autocomplete="username">
          </div>
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <div class="input-with-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
              <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
            <input type="password" id="password" name="password" class="form-control" placeholder="Enter password" required autocomplete="current-password">
            <button type="button" id="toggle-password" class="password-toggle-btn" title="Show/Hide Password">👁</button>
          </div>
        </div>

        <button type="submit" id="login-submit-btn" class="btn-primary" style="width: 100%; justify-content: center; margin-top: 15px;">
          <span>Sign In to Dashboard</span>
          <span style="font-size: 1.1rem;">→</span>
        </button>

        <div style="text-align: center; margin-top: 24px;">
          <a href="../index.html" class="back-link">
            <span>← Return to Public Portfolio</span>
          </a>
        </div>
      </form>
    </div>
  </div>

  <div class="toast-container"></div>

  <script>
    const form = document.getElementById('admin-login-form');
    const alertBox = document.getElementById('login-alert');
    const submitBtn = document.getElementById('login-submit-btn');
    const togglePass = document.getElementById('toggle-password');
    const passInput = document.getElementById('password');

    togglePass.addEventListener('click', () => {
      if (passInput.type === 'password') {
        passInput.type = 'text';
        togglePass.textContent = '🔒';
      } else {
        passInput.type = 'password';
        togglePass.textContent = '👁';
      }
    });

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      alertBox.style.display = 'none';

      const username = document.getElementById('username').value.trim();
      const password = document.getElementById('password').value;

      submitBtn.disabled = true;
      submitBtn.innerHTML = '<span>Verifying Credentials...</span>';

      try {
        const res = await fetch('../api/auth.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ action: 'login', username, password })
        });
        const data = await res.json();

        if (res.ok && data.success) {
          submitBtn.innerHTML = '<span>✓ Access Granted. Redirecting...</span>';
          setTimeout(() => {
            window.location.href = 'index.php';
          }, 600);
        } else {
          alertBox.className = 'admin-alert alert-error';
          alertBox.textContent = data.error || 'Authentication failed. Please verify credentials.';
          alertBox.style.display = 'block';
          submitBtn.disabled = false;
          submitBtn.innerHTML = '<span>Sign In to Dashboard</span><span style="font-size: 1.1rem;">→</span>';
        }
      } catch (err) {
        alertBox.className = 'admin-alert alert-error';
        alertBox.textContent = 'Network or server error during sign in.';
        alertBox.style.display = 'block';
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<span>Sign In to Dashboard</span><span style="font-size: 1.1rem;">→</span>';
      }
    });
  </script>
</body>
</html>
