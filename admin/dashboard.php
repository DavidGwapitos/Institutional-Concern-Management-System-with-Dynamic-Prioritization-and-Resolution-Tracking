<?php
// admin/dashboard.php - Administrator Dashboard (Figure 10 of Prototype)
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireAdmin();
$user = getCurrentUser();

// Fetch system-wide metrics matching Figure 10
$metrics = $pdo->query("
    SELECT 
        COUNT(*) AS total,
        SUM(CASE WHEN s.status_name = 'Pending' THEN 1 ELSE 0 END) AS pending,
        SUM(CASE WHEN s.status_name = 'In Progress' THEN 1 ELSE 0 END) AS in_progress,
        SUM(CASE WHEN s.status_name = 'Resolved' THEN 1 ELSE 0 END) AS resolved
    FROM concerns c
    JOIN statuses s ON c.status_id = s.status_id
")->fetch() ?: ['total' => 0, 'pending' => 0, 'in_progress' => 0, 'resolved' => 0];

// Fetch active Critical / Rapid Response Team alerts
$criticalAlerts = $pdo->query("
    SELECT c.*, s.name AS student_name, cat.category_name, st.status_name
    FROM concerns c
    JOIN students s ON c.student_id = s.student_id
    JOIN categories cat ON c.category_id = cat.category_id
    JOIN statuses st ON c.status_id = st.status_id
    WHERE (c.priority = 'Critical' OR c.is_rrt_alert = 1) AND st.status_name != 'Resolved'
    ORDER BY c.date_submitted DESC
")->fetchAll();

// Fetch recent 7 concerns matching Figure 10
$recentConcerns = $pdo->query("
    SELECT c.*, s.name AS student_name, cat.category_name, st.status_name
    FROM concerns c
    JOIN students s ON c.student_id = s.student_id
    JOIN categories cat ON c.category_id = cat.category_id
    JOIN statuses st ON c.status_id = st.status_id
    ORDER BY c.date_submitted DESC
    LIMIT 7
")->fetchAll();

$pageTitle = "Admin Dashboard | ICMS-DPT-RRT";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<!-- Page Header (Figure 10) -->
<div class="page-header">
  <div class="page-title-wrap">
    <h1 class="page-title">Welcome, Administrator!</h1>
    <p class="page-desc">Here's the overview of all concerns across the institution.</p>
  </div>
  <div style="display: flex; gap: 10px;">
    <a href="concerns.php" class="btn btn-primary">
      <span><?= icon('clipboard') ?> Manage All Concerns</span>
    </a>
    <a href="reports.php" class="btn btn-secondary">
      <span><?= icon('bar-chart') ?> Generate Report</span>
    </a>
  </div>
</div>

<!-- Rapid Response Team (RRT) Urgent Alert Feed -->
<?php if (!empty($criticalAlerts)): ?>
  <?php foreach ($criticalAlerts as $crit): ?>
    <div class="rrt-alert-banner animate-fade-in">
      <div class="rrt-alert-left">
        <?= icon('zap', '', 28) ?>
        <div>
          <div style="display: flex; align-items: center; gap: 8px;">
            <span class="rrt-alert-badge">RRT CRITICAL ALERT</span>
            <strong style="font-size: 1.05rem;"><?= e($crit['ticket_id']) ?>: <?= e($crit['title']) ?></strong>
            <span class="priority-pill priority-critical">Score: <?= (int)$crit['ai_urgency_score'] ?>/100</span>
          </div>
          <p style="font-size: 0.85rem; margin-top: 4px; opacity: 0.95;">
            Submitted by <?= e($crit['student_name']) ?> in <strong><?= e($crit['category_name']) ?></strong> • Target SLA: <strong>24 Hours</strong>. Immediate emergency response required!
          </p>
        </div>
      </div>
      <a href="concern_details.php?id=<?= $crit['concern_id'] ?>" class="btn btn-secondary btn-sm" style="color: #991b1b; font-weight: 700; background: #ffffff; display: inline-flex; align-items: center; gap: 6px;">
        <span>Respond Now</span> <?= icon('arrow-right', '', 14) ?>
      </a>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

<!-- 4 Metric Cards Grid (Figure 10) -->
<div class="metrics-grid">
  <!-- Total Concerns -->
  <div class="metric-card">
    <div class="metric-icon-wrap metric-icon-total"><?= icon('folder', '', 22) ?></div>
    <div class="metric-content">
      <span class="metric-label">Total Concerns</span>
      <span class="metric-number"><?= (int)$metrics['total'] ?></span>
    </div>
  </div>

  <!-- Pending (12 in Figure 10) -->
  <div class="metric-card">
    <div class="metric-icon-wrap metric-icon-pending"><?= icon('clock', '', 22) ?></div>
    <div class="metric-content">
      <span class="metric-label">Pending</span>
      <span class="metric-number"><?= (int)$metrics['pending'] ?></span>
    </div>
  </div>

  <!-- In Progress (10 in Figure 10) -->
  <div class="metric-card">
    <div class="metric-icon-wrap metric-icon-progress"><?= icon('refresh', '', 22) ?></div>
    <div class="metric-content">
      <span class="metric-label">In Progress</span>
      <span class="metric-number"><?= (int)$metrics['in_progress'] ?></span>
    </div>
  </div>

  <!-- Resolved (10 in Figure 10) -->
  <div class="metric-card">
    <div class="metric-icon-wrap metric-icon-resolved"><?= icon('check-circle', '', 22) ?></div>
    <div class="metric-content">
      <span class="metric-label">Resolved</span>
      <span class="metric-number"><?= (int)$metrics['resolved'] ?></span>
    </div>
  </div>
</div>

<!-- Recent Concerns Table (Figure 10) -->
<div class="card">
  <div class="card-header">
    <h2 class="card-title">Recent Concerns</h2>
    <a href="concerns.php" class="text-sm font-semibold" style="color: var(--brand-blue); display: inline-flex; align-items: center; gap: 4px;">View All Concerns <?= icon('arrow-right', '', 14) ?></a>
  </div>
  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th>Ticket ID</th>
          <th>Title</th>
          <th>Category</th>
          <th>Submitted By</th>
          <th>Date Submitted</th>
          <th>Priority / SLA</th>
          <th>Status</th>
          <th style="text-align: right;">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recentConcerns as $concern): ?>
          <tr>
            <td>
              <span class="ticket-badge"><?= e($concern['ticket_id']) ?></span>
            </td>
            <td style="font-weight: 600;">
              <a href="concern_details.php?id=<?= $concern['concern_id'] ?>" style="color: var(--text-primary);">
                <?= e($concern['title']) ?>
              </a>
            </td>
            <td><?= e($concern['category_name']) ?></td>
            <td><?= e($concern['student_name']) ?></td>
            <td class="text-muted text-sm"><?= formatDate($concern['date_submitted']) ?></td>
            <td><?= renderPriorityBadge($concern['priority'], $concern['ai_urgency_score']) ?></td>
            <td><?= renderStatusBadge($concern['status_name']) ?></td>
            <td style="text-align: right;">
              <div style="display: inline-flex; gap: 4px;">
                <a href="concern_details.php?id=<?= $concern['concern_id'] ?>" class="btn-icon" title="View & Respond">
                  <?= icon('eye') ?>
                </a>
                <a href="concern_details.php?id=<?= $concern['concern_id'] ?>#responseBox" class="btn-icon" title="Update Status">
                  <?= icon('edit') ?>
                </a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
