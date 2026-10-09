<?php
/**
 * Admin Login Page with 2-Factor OTP Security
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
        <div class="login-badge" id="step-badge">CONTROL PANEL // SECURE ACCESS</div>
        <h1 class="login-title" id="step-title">ADMIN LOGIN</h1>
        <p class="login-subtitle" id="step-subtitle">Authenticate to manage portfolio projects, client inquiries, and site content.</p>
      </div>

      <div id="login-alert" class="admin-alert" style="display: none;"></div>

      <!-- Step 1: Credentials Form -->
      <form id="admin-login-form">
        <div class="form-group">
          <label for="username">Username or Email</label>
          <div class="input-with-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
              <circle cx="12" cy="7" r="4"></circle>
            </svg>
            <input type="text" id="username" name="username" class="form-control" placeholder="admin or Briantanael187@gmail.com" required autofocus autocomplete="username">
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

      <!-- Step 2: 2FA OTP Form -->
      <form id="admin-otp-form" style="display: none;">
        <div class="form-group" style="text-align: center;">
          <label for="otp-code" style="font-size: 0.85rem; letter-spacing: 1.5px; text-transform: uppercase;">Enter 6-Digit Verification Code</label>
          <input type="text" id="otp-code" name="otp" class="form-control" 
            placeholder="000000" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" required 
            style="font-size: 2.2rem; font-family: var(--font-mono); letter-spacing: 12px; text-align: center; font-weight: 800; background: rgba(0,0,0,0.5); border-color: var(--admin-accent); color: #fff; padding: 14px 10px;">
          <small style="color: var(--admin-text-muted); display: block; margin-top: 8px;">Code valid for 10 minutes</small>
        </div>

        <button type="submit" id="otp-submit-btn" class="btn-primary" style="width: 100%; justify-content: center; margin-top: 14px;">
          <span>Verify & Access Dashboard</span>
          <span style="font-size: 1.1rem;">⚡</span>
        </button>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; font-size: 0.82rem;">
          <button type="button" id="resend-otp-btn" class="back-link" style="background: none; border: none; cursor: pointer; padding: 0;">
            <span>Resend Code</span>
          </button>
          <button type="button" id="back-to-login-btn" class="back-link" style="background: none; border: none; cursor: pointer; padding: 0;">
            <span>← Back to Credentials</span>
          </button>
        </div>
      </form>

    </div>
  </div>

  <div class="toast-container"></div>

  <script>
    const loginForm = document.getElementById('admin-login-form');
    const otpForm = document.getElementById('admin-otp-form');
    const alertBox = document.getElementById('login-alert');
    const submitBtn = document.getElementById('login-submit-btn');
    const otpSubmitBtn = document.getElementById('otp-submit-btn');
    const togglePass = document.getElementById('toggle-password');
    const passInput = document.getElementById('password');
    const otpInput = document.getElementById('otp-code');
    const resendBtn = document.getElementById('resend-otp-btn');
    const backToLoginBtn = document.getElementById('back-to-login-btn');

    const stepBadge = document.getElementById('step-badge');
    const stepTitle = document.getElementById('step-title');
    const stepSubtitle = document.getElementById('step-subtitle');

    let resendCountdown = 0;
    let resendInterval = null;

    togglePass.addEventListener('click', () => {
      if (passInput.type === 'password') {
        passInput.type = 'text';
        togglePass.textContent = '🔒';
      } else {
        passInput.type = 'password';
        togglePass.textContent = '👁';
      }
    });

    // Step 1: Credentials Submission
    loginForm.addEventListener('submit', async (e) => {
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
          if (data.require_otp) {
            // Transition to Step 2: OTP
            loginForm.style.display = 'none';
            otpForm.style.display = 'block';

            stepBadge.textContent = 'SECURITY // 2-FACTOR VERIFICATION';
            stepTitle.textContent = 'ENTER OTP';
            stepSubtitle.innerHTML = `A 6-digit verification code has been dispatched to <strong style="color: var(--admin-accent);">${data.full_email || data.email}</strong>.`;

            alertBox.className = 'admin-alert alert-info';
            alertBox.innerHTML = `✓ Verification code sent to <strong>${data.full_email || data.email}</strong>. Please check your Gmail.`;
            alertBox.style.display = 'block';

            startResendTimer(30);
            otpInput.focus();
          } else {
            // Direct login
            submitBtn.innerHTML = '<span>✓ Access Granted. Redirecting...</span>';
            setTimeout(() => {
              window.location.href = 'index.php';
            }, 600);
          }
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

    // Step 2: OTP Verification
    otpForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      alertBox.style.display = 'none';

      const otp = otpInput.value.trim();
      if (otp.length !== 6) {
        alertBox.className = 'admin-alert alert-error';
        alertBox.textContent = 'Please enter all 6 digits of the verification code.';
        alertBox.style.display = 'block';
        return;
      }

      otpSubmitBtn.disabled = true;
      otpSubmitBtn.innerHTML = '<span>Verifying Code...</span>';

      try {
        const res = await fetch('../api/auth.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ action: 'verify_otp', otp })
        });
        const data = await res.json();

        if (res.ok && data.success) {
          alertBox.className = 'admin-alert alert-success';
          alertBox.textContent = '✓ Verified! Redirecting to Admin Dashboard...';
          alertBox.style.display = 'block';
          otpSubmitBtn.innerHTML = '<span>✓ Access Granted</span>';
          setTimeout(() => {
            window.location.href = 'index.php';
          }, 600);
        } else {
          alertBox.className = 'admin-alert alert-error';
          alertBox.textContent = data.error || 'Invalid verification code.';
          alertBox.style.display = 'block';
          otpSubmitBtn.disabled = false;
          otpSubmitBtn.innerHTML = '<span>Verify & Access Dashboard</span><span style="font-size: 1.1rem;">⚡</span>';
          otpInput.select();
        }
      } catch (err) {
        alertBox.className = 'admin-alert alert-error';
        alertBox.textContent = 'Network or server error during OTP verification.';
        alertBox.style.display = 'block';
        otpSubmitBtn.disabled = false;
        otpSubmitBtn.innerHTML = '<span>Verify & Access Dashboard</span><span style="font-size: 1.1rem;">⚡</span>';
      }
    });

    // Auto-submit when 6 digits are typed
    otpInput.addEventListener('input', () => {
      otpInput.value = otpInput.value.replace(/[^0-9]/g, '');
      if (otpInput.value.length === 6) {
        otpForm.dispatchEvent(new Event('submit'));
      }
    });

    // Resend OTP
    resendBtn.addEventListener('click', async () => {
      if (resendCountdown > 0) return;

      resendBtn.disabled = true;
      resendBtn.textContent = 'Dispatching new code...';

      try {
        const res = await fetch('../api/auth.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ action: 'resend_otp' })
        });
        const data = await res.json();

        if (res.ok && data.success) {
          alertBox.className = 'admin-alert alert-info';
          alertBox.textContent = '✓ A new verification code has been dispatched to your email.';
          alertBox.style.display = 'block';
          startResendTimer(30);
        } else {
          alertBox.className = 'admin-alert alert-error';
          alertBox.textContent = data.error || 'Failed to resend code.';
          alertBox.style.display = 'block';
          resendBtn.disabled = false;
          resendBtn.textContent = 'Resend Code';
        }
      } catch (err) {
        resendBtn.disabled = false;
        resendBtn.textContent = 'Resend Code';
      }
    });

    // Back to credentials form
    backToLoginBtn.addEventListener('click', () => {
      otpForm.style.display = 'none';
      loginForm.style.display = 'block';
      alertBox.style.display = 'none';

      stepBadge.textContent = 'CONTROL PANEL // SECURE ACCESS';
      stepTitle.textContent = 'ADMIN LOGIN';
      stepSubtitle.textContent = 'Authenticate to manage portfolio projects, client inquiries, and site content.';

      submitBtn.disabled = false;
      submitBtn.innerHTML = '<span>Sign In to Dashboard</span><span style="font-size: 1.1rem;">→</span>';
    });

    function startResendTimer(seconds) {
      resendCountdown = seconds;
      resendBtn.disabled = true;
      resendBtn.textContent = `Resend Code (${resendCountdown}s)`;

      if (resendInterval) clearInterval(resendInterval);
      resendInterval = setInterval(() => {
        resendCountdown--;
        if (resendCountdown <= 0) {
          clearInterval(resendInterval);
          resendBtn.disabled = false;
          resendBtn.textContent = 'Resend Code';
        } else {
          resendBtn.textContent = `Resend Code (${resendCountdown}s)`;
        }
      }, 1000);
    }
  </script>
</body>
</html>
