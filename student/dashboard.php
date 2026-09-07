<?php
// student/dashboard.php - Student Dashboard (Figure 2 of Prototype)
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireStudent();
$user = getCurrentUser();
$studentId = $user['id'];

// Fetch metrics for this student
$metricsStmt = $pdo->prepare("
    SELECT 
        COUNT(*) AS total,
        SUM(CASE WHEN s.status_name = 'Pending' THEN 1 ELSE 0 END) AS pending,
        SUM(CASE WHEN s.status_name = 'In Progress' THEN 1 ELSE 0 END) AS in_progress,
        SUM(CASE WHEN s.status_name = 'Resolved' THEN 1 ELSE 0 END) AS resolved
    FROM concerns c
    JOIN statuses s ON c.status_id = s.status_id
    WHERE c.student_id = :student_id
");
$metricsStmt->execute(['student_id' => $studentId]);
$metrics = $metricsStmt->fetch() ?: ['total' => 0, 'pending' => 0, 'in_progress' => 0, 'resolved' => 0];

// Fetch recent 5 concerns for this student
$recentStmt = $pdo->prepare("
    SELECT c.*, cat.category_name, s.status_name 
    FROM concerns c
    JOIN categories cat ON c.category_id = cat.category_id
    JOIN statuses s ON c.status_id = s.status_id
    WHERE c.student_id = :student_id
    ORDER BY c.date_submitted DESC
    LIMIT 5
");
$recentStmt->execute(['student_id' => $studentId]);
$recentConcerns = $recentStmt->fetchAll();

// Fetch latest urgent or general announcements
$announcements = $pdo->query("
    SELECT * FROM announcements 
    ORDER BY is_urgent DESC, published_at DESC 
    LIMIT 3
")->fetchAll();

$pageTitle = "Student Dashboard | ICMS-DPT-RRT";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<!-- Page Greeting (Figure 2) -->
<div class="page-header">
  <div class="page-title-wrap">
    <h1 class="page-title">Welcome, <?= e($user['name']) ?>!</h1>
    <p class="page-desc">Here's what's happening with your concerns.</p>
  </div>
  <a href="submit_concern.php" class="btn btn-primary">
    <span><?= icon('edit') ?> Submit New Concern</span>
  </a>
</div>

<!-- 4 Metric Cards Grid (Figure 2) -->
<div class="metrics-grid">
  <!-- Total Concerns -->
  <div class="metric-card">
    <div class="metric-icon-wrap metric-icon-total"><?= icon('clipboard', '', 24) ?></div>
    <div class="metric-content">
      <span class="metric-label">Total Concerns</span>
      <span class="metric-number"><?= (int)$metrics['total'] ?></span>
    </div>
  </div>

  <!-- Pending -->
  <div class="metric-card">
    <div class="metric-icon-wrap metric-icon-pending"><?= icon('clock', '', 24) ?></div>
    <div class="metric-content">
      <span class="metric-label">Pending</span>
      <span class="metric-number"><?= (int)$metrics['pending'] ?></span>
    </div>
  </div>

  <!-- In Progress -->
  <div class="metric-card">
    <div class="metric-icon-wrap metric-icon-progress"><?= icon('refresh', '', 24) ?></div>
    <div class="metric-content">
      <span class="metric-label">In Progress</span>
      <span class="metric-number"><?= (int)$metrics['in_progress'] ?></span>
    </div>
  </div>

  <!-- Resolved -->
  <div class="metric-card">
    <div class="metric-icon-wrap metric-icon-resolved"><?= icon('check-circle', '', 24) ?></div>
    <div class="metric-content">
      <span class="metric-label">Resolved</span>
      <span class="metric-number"><?= (int)$metrics['resolved'] ?></span>
    </div>
  </div>
</div>

<!-- Recent Concerns Table (Figure 2) -->
<div class="card">
  <div class="card-header">
    <h2 class="card-title">Recent Concerns</h2>
    <a href="my_concerns.php" class="text-sm font-semibold" style="color: var(--brand-blue); display: inline-flex; align-items: center; gap: 4px;">View All <?= icon('arrow-right', '', 14) ?></a>
  </div>
  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th>Ticket ID</th>
          <th>Concern Title</th>
          <th>Category</th>
          <th>Date Submitted</th>
          <th>Priority</th>
          <th>Status</th>
          <th style="text-align: right;">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($recentConcerns)): ?>
          <tr>
            <td colspan="7" style="text-align: center; padding: 32px; color: var(--text-muted);">
              No concerns submitted yet. Click <strong>"Submit New Concern"</strong> above to report an issue!
            </td>
          </tr>
        <?php else: ?>
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
              <td class="text-muted text-sm"><?= formatDate($concern['date_submitted']) ?></td>
              <td><?= renderPriorityBadge($concern['priority'], $concern['ai_urgency_score']) ?></td>
              <td><?= renderStatusBadge($concern['status_name']) ?></td>
              <td style="text-align: right;">
                <a href="concern_details.php?id=<?= $concern['concern_id'] ?>" class="btn-icon" title="View Concern Details">
                  <?= icon('eye', '', 15) ?>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Announcements Preview (Figure 7 preview on dashboard) -->
<?php if (!empty($announcements)): ?>
<div class="card">
  <div class="card-header">
    <h2 class="card-title" style="display: flex; align-items: center; gap: 8px;">
      <?= icon('megaphone') ?> <span>Latest Announcements</span>
    </h2>
    <a href="announcements.php" class="text-sm font-semibold" style="color: var(--brand-blue); display: inline-flex; align-items: center; gap: 4px;">View All Bulletins <?= icon('arrow-right', '', 14) ?></a>
  </div>
  <div class="card-body" style="display: flex; flex-direction: column; gap: 14px;">
    <?php foreach ($announcements as $anc): ?>
      <div style="display: flex; align-items: flex-start; gap: 14px; padding-bottom: 12px; border-bottom: 1px solid var(--border-subtle);">
        <div style="width: 36px; height: 36px; border-radius: var(--radius-sm); background: #eff6ff; display: flex; align-items: center; justify-content: center; color: <?= $anc['is_urgent'] ? '#dc2626' : '#2563eb' ?>;">
          <?= $anc['is_urgent'] ? icon('alert-triangle', '', 18) : icon('megaphone', '', 18) ?>
        </div>
        <div style="flex: 1;">
          <div style="display: flex; align-items: center; gap: 8px;">
            <strong style="font-size: 0.95rem;"><?= e($anc['title']) ?></strong>
            <?php if ($anc['is_urgent']): ?>
              <span class="priority-pill priority-critical">Urgent</span>
            <?php endif; ?>
            <span class="text-xs text-muted"><?= formatDate($anc['published_at']) ?></span>
          </div>
          <p style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 4px;">
            <?= e($anc['content']) ?>
          </p>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
