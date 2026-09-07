<?php
// includes/navbar.php - Top Navigation Bar (Figures 2 & 10)
$user = getCurrentUser();
$isSubdir = (strpos($_SERVER['SCRIPT_NAME'], '/student/') !== false || strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false);
$rootPrefix = $isSubdir ? '../' : './';
$userName = $user ? e($user['name']) : 'Guest User';
$userRole = $user ? ucfirst(e($user['role'])) : 'Guest';
$profileUrl = ($user && $user['role'] === 'admin') ? $rootPrefix . 'admin/profile.php' : $rootPrefix . 'student/profile.php';
?>
<header class="top-header">
  <div style="display: flex; align-items: center;">
    <button type="button" class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle menu">
      <?= icon('menu', '', 22) ?>
    </button>
    <a href="<?= ($user && $user['role'] === 'admin') ? $rootPrefix . 'admin/dashboard.php' : $rootPrefix . 'student/dashboard.php' ?>" class="header-brand">
      <div class="brand-emblem">ICMS</div>
      <div class="brand-text">
        <span class="brand-title">Student Concern & Feedback Management</span>
        <span class="brand-subtitle">Status Tracking (DPT-RRT) • SNSU CCIS</span>
      </div>
    </a>
  </div>

  <div class="header-actions">
    <!-- Notification Bell & Interactive Dropdown -->
    <div class="header-dropdown-wrap">
      <button type="button" class="header-icon-btn" id="notificationBtn" title="Notifications" aria-label="Notifications" onclick="toggleNotificationDropdown(event)">
        <?= icon('bell') ?>
        <span class="header-badge-count" id="notifBadgeCount">3</span>
      </button>

      <div class="notification-dropdown" id="notificationDropdown">
        <div class="notification-dropdown-header">
          <div style="display: flex; align-items: center; gap: 8px;">
            <span style="font-weight: 700; color: var(--navy-primary); display: flex; align-items: center; gap: 6px;">
              <?= icon('bell', '', 16) ?> Notifications
            </span>
            <span class="badge-tag" style="background: #fee2e2; color: #dc2626; font-size: 0.7rem;" id="notifUnreadBadge">3 New</span>
          </div>
          <button type="button" class="notif-action-link" onclick="markAllNotificationsRead(event)">Mark all read</button>
        </div>

        <div class="notification-dropdown-body">
          <div class="notification-item unread" onclick="showToast('Rapid Response Alert: Critical electrical hazard in Lab 2 has been flagged for immediate emergency SLA response.', 'error', 'RRT Critical Alert');">
            <div class="notification-icon-wrap" style="background: #fee2e2; color: #dc2626;">
              <?= icon('zap', '', 16) ?>
            </div>
            <div class="notification-text">
              <div class="notification-title">Rapid Response Team Alert</div>
              <div class="notification-desc">Critical hazard in Lab 2 flagged for emergency SLA.</div>
              <div class="notification-time"><?= icon('clock', '', 12) ?> 10 minutes ago</div>
            </div>
          </div>

          <div class="notification-item unread" onclick="showToast('Ticket SCF-2024-002 has been officially endorsed to Facilities & Maintenance.', 'info', 'Status Update');">
            <div class="notification-icon-wrap" style="background: #eff6ff; color: #2563eb;">
              <?= icon('refresh', '', 16) ?>
            </div>
            <div class="notification-text">
              <div class="notification-title">Status Transition</div>
              <div class="notification-desc">Ticket SCF-2024-002 was moved to In Progress.</div>
              <div class="notification-time"><?= icon('clock', '', 12) ?> 1 hour ago</div>
            </div>
          </div>

          <div class="notification-item unread" onclick="showToast('Routine maintenance scheduled for campus servers this coming Saturday at 10:00 PM.', 'warning', 'Campus Notice');">
            <div class="notification-icon-wrap" style="background: #fef3c7; color: #d97706;">
              <?= icon('megaphone', '', 16) ?>
            </div>
            <div class="notification-text">
              <div class="notification-title">Scheduled System Maintenance</div>
              <div class="notification-desc">Routine campus server maintenance scheduled.</div>
              <div class="notification-time"><?= icon('clock', '', 12) ?> 3 hours ago</div>
            </div>
          </div>
        </div>

        <div class="notification-dropdown-footer">
          <a href="announcements.php" style="display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 0.8rem; font-weight: 600; color: var(--brand-blue); text-decoration: none;">
            <span>View All Announcements</span> <?= icon('arrow-right', '', 14) ?>
          </a>
        </div>
      </div>
    </div>

    <!-- User Profile Pill (Figures 2 & 10) -->
    <a href="<?= $profileUrl ?>" class="header-user-btn" title="View Profile">
      <div class="header-avatar"><?= strtoupper(substr($userName, 0, 1)) ?></div>
      <div class="header-user-info">
        <span class="header-user-name"><?= $userName ?></span>
        <span class="header-user-role"><?= $userRole ?></span>
      </div>
    </a>
  </div>
</header>
<div class="app-container">
