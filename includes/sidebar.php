<?php
// includes/sidebar.php - Dynamic Sidebar Navigation (Figures 2 & 10)
$user = getCurrentUser();
$role = $user['role'] ?? 'student';
$currentScript = basename($_SERVER['SCRIPT_NAME']);
$isSubdir = (strpos($_SERVER['SCRIPT_NAME'], '/student/') !== false || strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false);
$rootPrefix = $isSubdir ? '../' : './';
?>
<aside class="app-sidebar" id="appSidebar">
  <ul class="sidebar-menu">
    <?php if ($role === 'student'): ?>
      <!-- Student Portal Navigation (Figure 2) -->
      <li>
        <a href="<?= $rootPrefix ?>student/dashboard.php" class="sidebar-link <?= ($currentScript === 'dashboard.php') ? 'active' : '' ?>">
          <span class="sidebar-icon"><?= icon('dashboard') ?></span>
          <span>Dashboard</span>
        </a>
      </li>
      <li>
        <a href="<?= $rootPrefix ?>student/submit_concern.php" class="sidebar-link <?= ($currentScript === 'submit_concern.php') ? 'active' : '' ?>">
          <span class="sidebar-icon"><?= icon('edit') ?></span>
          <span>Submit Concern</span>
        </a>
      </li>
      <li>
        <a href="<?= $rootPrefix ?>student/my_concerns.php" class="sidebar-link <?= ($currentScript === 'my_concerns.php' || $currentScript === 'concern_details.php') ? 'active' : '' ?>">
          <span class="sidebar-icon"><?= icon('clipboard') ?></span>
          <span>My Concerns</span>
        </a>
      </li>
      <li>
        <a href="<?= $rootPrefix ?>student/feedback.php" class="sidebar-link <?= ($currentScript === 'feedback.php') ? 'active' : '' ?>">
          <span class="sidebar-icon"><?= icon('message-square') ?></span>
          <span>Feedback</span>
        </a>
      </li>
      <li>
        <a href="<?= $rootPrefix ?>student/announcements.php" class="sidebar-link <?= ($currentScript === 'announcements.php') ? 'active' : '' ?>">
          <span class="sidebar-icon"><?= icon('megaphone') ?></span>
          <span>Announcements</span>
        </a>
      </li>
      <li>
        <a href="<?= $rootPrefix ?>student/profile.php" class="sidebar-link <?= ($currentScript === 'profile.php') ? 'active' : '' ?>">
          <span class="sidebar-icon"><?= icon('user') ?></span>
          <span>Profile</span>
        </a>
      </li>
    <?php else: ?>
      <!-- Administrator Portal Navigation (Figure 10) -->
      <li>
        <a href="<?= $rootPrefix ?>admin/dashboard.php" class="sidebar-link <?= ($currentScript === 'dashboard.php') ? 'active' : '' ?>">
          <span class="sidebar-icon"><?= icon('dashboard') ?></span>
          <span>Dashboard</span>
        </a>
      </li>
      <li>
        <a href="<?= $rootPrefix ?>admin/concerns.php" class="sidebar-link <?= ($currentScript === 'concerns.php' || $currentScript === 'concern_details.php') ? 'active' : '' ?>">
          <span class="sidebar-icon"><?= icon('file-text') ?></span>
          <span>Manage Concerns</span>
        </a>
      </li>
      <li>
        <a href="<?= $rootPrefix ?>admin/users.php" class="sidebar-link <?= ($currentScript === 'users.php') ? 'active' : '' ?>">
          <span class="sidebar-icon"><?= icon('users') ?></span>
          <span>Users</span>
        </a>
      </li>
      <li>
        <a href="<?= $rootPrefix ?>admin/announcements.php" class="sidebar-link <?= ($currentScript === 'announcements.php') ? 'active' : '' ?>">
          <span class="sidebar-icon"><?= icon('megaphone') ?></span>
          <span>Announcements</span>
        </a>
      </li>
      <li>
        <a href="<?= $rootPrefix ?>admin/feedback.php" class="sidebar-link <?= ($currentScript === 'feedback.php') ? 'active' : '' ?>">
          <span class="sidebar-icon"><?= icon('star') ?></span>
          <span>Feedback</span>
        </a>
      </li>
      <li>
        <a href="<?= $rootPrefix ?>admin/reports.php" class="sidebar-link <?= ($currentScript === 'reports.php') ? 'active' : '' ?>">
          <span class="sidebar-icon"><?= icon('bar-chart') ?></span>
          <span>Reports & Monitoring</span>
        </a>
      </li>
      <li>
        <a href="<?= $rootPrefix ?>admin/profile.php" class="sidebar-link <?= ($currentScript === 'profile.php') ? 'active' : '' ?>">
          <span class="sidebar-icon"><?= icon('user') ?></span>
          <span>Profile</span>
        </a>
      </li>
    <?php endif; ?>

    <li class="sidebar-divider"></li>
    <li>
      <a href="javascript:void(0)" onclick="openModal('logoutModal')" class="sidebar-link" style="color: #ef4444;">
        <span class="sidebar-icon"><?= icon('log-out') ?></span>
        <span>Logout</span>
      </a>
    </li>
  </ul>
</aside>
<main class="app-main">
  <div class="main-content-container">
    <?php renderFlash(); ?>
