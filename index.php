<?php
// index.php - Login Page (Figure 1 of Prototype)
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/helpers.php';

if (!empty($_SESSION['user_id']) && !empty($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: student/dashboard.php');
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | Student Concern & Feedback Management System (ICMS-DPT-RRT)</title>
  <link rel="stylesheet" href="assets/css/variables.css">
  <link rel="stylesheet" href="assets/css/base.css">
  <link rel="stylesheet" href="assets/css/components.css">
  <style>
    body {
      background: linear-gradient(135deg, #091730 0%, #0e2246 50%, #1e3a8a 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px;
    }

    .login-container {
      width: 100%;
      max-width: 960px;
      min-height: 560px;
      background: #ffffff;
      border-radius: var(--radius-xl);
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
      display: flex;
      overflow: hidden;
    }

    /* Left Hero Panel (Matching Figure 1) */
    .login-hero {
      flex: 1.1;
      background: linear-gradient(145deg, #0e2246 0%, #162c5b 60%, #1d4ed8 100%);
      color: #ffffff;
      padding: 48px 40px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      position: relative;
    }

    .hero-top {
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
      margin-top: 20px;
    }

    .hero-icon-bubble {
      width: 80px;
      height: 80px;
      background: rgba(255, 255, 255, 0.12);
      border: 2px solid rgba(255, 255, 255, 0.25);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 2.5rem;
      margin-bottom: 24px;
      box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    }

    .hero-title {
      font-size: 1.65rem;
      font-weight: 800;
      line-height: 1.3;
      color: #ffffff;
      margin-bottom: 8px;
    }

    .hero-subtitle {
      font-size: 0.95rem;
      color: #93c5fd;
      font-weight: 600;
      letter-spacing: 0.05em;
      text-transform: uppercase;
    }

    .hero-badges {
      display: flex;
      gap: 10px;
      justify-content: center;
      margin-top: 24px;
    }

    .hero-badge {
      font-size: 0.75rem;
      background: rgba(255, 255, 255, 0.15);
      padding: 4px 12px;
      border-radius: var(--radius-pill);
      border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .hero-footer {
      text-align: center;
      font-size: 0.85rem;
      color: #bfdbfe;
      font-style: italic;
      border-top: 1px solid rgba(255, 255, 255, 0.15);
      padding-top: 20px;
    }

    /* Right Form Panel (Matching Figure 1) */
    .login-form-pane {
      flex: 1;
      padding: 48px 44px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      background: #ffffff;
    }

    .form-header {
      margin-bottom: 24px;
      text-align: center;
    }

    .form-header h2 {
      font-size: 1.6rem;
      color: var(--text-primary);
      margin-bottom: 4px;
    }

    .form-header p {
      font-size: 0.875rem;
      color: var(--text-muted);
    }

    .input-icon-wrap {
      position: relative;
    }

    .input-icon-wrap input {
      padding-left: 40px;
    }

    .field-icon {
      position: absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      color: #94a3b8;
      font-size: 1rem;
    }

    .login-options {
      display: flex;
      align-items: center;
      justify-content: flex-end;
      margin-bottom: 20px;
      font-size: 0.825rem;
    }

    .divider-or {
      display: flex;
      align-items: center;
      text-align: center;
      margin: 18px 0;
      color: #94a3b8;
      font-size: 0.8rem;
      font-weight: 600;
    }

    .divider-or::before,
    .divider-or::after {
      content: '';
      flex: 1;
      border-bottom: 1px solid var(--border-color);
    }

    .divider-or span {
      padding: 0 12px;
    }

    .demo-bar {
      margin-top: 16px;
      padding: 10px 14px;
      background: #f8fafc;
      border: 1px dashed var(--border-color);
      border-radius: var(--radius-md);
      font-size: 0.775rem;
      display: flex;
      flex-direction: column;
      gap: 6px;
    }

    .demo-buttons {
      display: flex;
      gap: 8px;
    }

    .demo-btn {
      flex: 1;
      padding: 4px 8px;
      font-size: 0.75rem;
      border-radius: var(--radius-xs);
      background: #ffffff;
      border: 1px solid var(--border-color);
      cursor: pointer;
      font-weight: 600;
      color: var(--text-secondary);
      transition: all 0.15s ease;
    }
    .demo-btn:hover {
      background: var(--brand-blue-light);
      color: var(--brand-blue);
      border-color: var(--brand-blue-border);
    }

    @media (max-width: 768px) {
      .login-container {
        flex-direction: column;
      }
      .login-hero {
        padding: 32px 24px;
      }
      .login-form-pane {
        padding: 32px 24px;
      }
    }
  </style>
</head>
<body>

<div class="login-container">
  <!-- Left Brand Hero (Figure 1) -->
  <div class="login-hero">
    <div class="hero-top">
      <div class="hero-icon-bubble">
        <?= icon('message-square', '', 38) ?>
      </div>
      <h1 class="hero-title">Student Concern & Feedback Management System</h1>
      <p class="hero-subtitle">With Status Tracking</p>

      <div class="hero-badges">
        <span class="hero-badge">AI Urgency Engine</span>
        <span class="hero-badge">SLA Tracking</span>
        <span class="hero-badge">Rapid Response</span>
      </div>
    </div>

    <div class="hero-footer">
      "Your voice matters. We're here to listen and act."
      <div style="font-size: 0.725rem; opacity: 0.8; margin-top: 4px;">Surigao del Norte State University (SNSU)</div>
    </div>
  </div>

  <!-- Right Login Form (Figure 1) -->
  <div class="login-form-pane">
    <div class="form-header">
      <h2 id="loginTitle">Welcome Back!</h2>
      <p id="loginSubtitle">Please sign in to continue</p>
    </div>

    <?php renderFlash(); ?>

    <form action="actions/auth_action.php" method="POST" id="loginForm">
      <input type="hidden" name="role" id="loginRole" value="student">

      <div class="form-group">
        <label class="form-label" for="identifier">Email or Username</label>
        <div class="input-icon-wrap">
          <span class="field-icon"><?= icon('user', '', 16) ?></span>
          <input type="text" name="identifier" id="identifier" class="form-control" placeholder="Enter your email or username" required autofocus>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <div class="input-icon-wrap">
          <span class="field-icon"><?= icon('lock', '', 16) ?></span>
          <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required>
        </div>
      </div>

      <div class="login-options">
        <a href="javascript:void(0)" onclick="showToast('For password resets, please coordinate directly with your campus administrator or MIS office.', 'info', 'Password Reset')" style="color: #64748b;">Forgot Password?</a>
      </div>

      <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;" id="submitBtn">
        <span><?= icon('arrow-right', '', 16) ?> Login</span>
      </button>

      <div class="divider-or">
        <span>OR</span>
      </div>

      <button type="button" class="btn btn-secondary" style="width: 100%;" id="toggleRoleBtn" onclick="toggleRoleMode()">
        <span><?= icon('shield', '', 16) ?> Login as Administrator</span>
      </button>

      <!-- 1-Click Demo Fill Bar for Thesis Defense Presentation -->
      <div class="demo-bar">
        <span style="font-weight: 700; color: #475569;">Quick Demo Accounts:</span>
        <div class="demo-buttons">
          <button type="button" class="demo-btn" onclick="fillStudentDemo()">
            <?= icon('user', '', 13) ?> Juan Dela Cruz (Student)
          </button>
          <button type="button" class="demo-btn" onclick="fillAdminDemo()">
            <?= icon('shield', '', 13) ?> Admin User (Admin)
          </button>
        </div>
      </div>

      <div style="text-align: center; margin-top: 18px; font-size: 0.8rem; color: var(--text-muted);">
        Don't have an account? <a href="javascript:void(0)" onclick="showToast('Student accounts are provisioned via SNSU registrar portal. Please use demo credentials.', 'info', 'Account Registration')" style="color: var(--brand-blue); font-weight: 600;">Contact Administrator</a>
      </div>
    </form>
  </div>
</div>

<script>
  let isAdministrator = false;

  function toggleRoleMode() {
    isAdministrator = !isAdministrator;
    const roleInput = document.getElementById('loginRole');
    const toggleBtn = document.getElementById('toggleRoleBtn');
    const loginTitle = document.getElementById('loginTitle');
    const loginSubtitle = document.getElementById('loginSubtitle');
    const submitBtn = document.getElementById('submitBtn');

    if (isAdministrator) {
      roleInput.value = 'admin';
      loginTitle.textContent = 'Administrator Portal';
      loginSubtitle.textContent = 'Sign in with administrative privileges';
      toggleBtn.innerHTML = '<span><?= icon("user", "", 16) ?> Login as Student</span>';
      submitBtn.style.backgroundColor = '#0e2246';
      submitBtn.style.borderColor = '#0e2246';
    } else {
      roleInput.value = 'student';
      loginTitle.textContent = 'Welcome Back!';
      loginSubtitle.textContent = 'Please sign in to continue';
      toggleBtn.innerHTML = '<span><?= icon("shield", "", 16) ?> Login as Administrator</span>';
      submitBtn.style.backgroundColor = 'var(--brand-blue)';
      submitBtn.style.borderColor = 'var(--brand-blue)';
    }
  }

  function fillStudentDemo() {
    if (isAdministrator) toggleRoleMode();
    document.getElementById('identifier').value = 'juan.delacruz@student.com';
    document.getElementById('password').value = 'student123';
  }

  function fillAdminDemo() {
    if (!isAdministrator) toggleRoleMode();
    document.getElementById('identifier').value = 'admin@school.edu';
    document.getElementById('password').value = 'admin123';
  }
</script>

<div class="toast-container" id="toastContainer"></div>
<script src="assets/js/main.js"></script>

</body>
</html>
