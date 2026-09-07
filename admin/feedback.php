<?php
// admin/feedback.php - Admin Feedback Management (Figure 13 of Prototype)
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireAdmin();

// Calculate feedback statistics
$stats = $pdo->query("
    SELECT 
        COUNT(*) as total_feedback,
        AVG(rating) as avg_rating,
        SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as stars_5,
        SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as stars_4,
        SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as stars_3,
        SUM(CASE WHEN rating <= 2 THEN 1 ELSE 0 END) as stars_low
    FROM feedback
")->fetch();

$allFeedback = $pdo->query("
    SELECT f.*, s.student_no 
    FROM feedback f
    LEFT JOIN students s ON f.student_id = s.student_id
    ORDER BY f.submitted_at DESC
")->fetchAll();

$pageTitle = "Feedback Management | ICMS-DPT-RRT";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="page-header">
  <div class="page-title-wrap">
    <h1 class="page-title">Feedback Management</h1>
    <p class="page-desc">Review student feedback and suggestions.</p>
  </div>
</div>

<!-- Summary Metrics -->
<div class="metrics-grid" style="margin-bottom: 24px;">
  <div class="metric-card">
    <div class="metric-icon-wrap metric-icon-total"><?= icon('star', '', 22) ?></div>
    <div class="metric-content">
      <span class="metric-label">Average Satisfaction</span>
      <span class="metric-number"><?= number_format((float)($stats['avg_rating'] ?: 5.0), 1) ?> <span style="font-size: 1rem; color: #f59e0b;">/ 5.0</span></span>
    </div>
  </div>

  <div class="metric-card">
    <div class="metric-icon-wrap metric-icon-progress"><?= icon('message-square', '', 22) ?></div>
    <div class="metric-content">
      <span class="metric-label">Total Feedback Received</span>
      <span class="metric-number"><?= (int)$stats['total_feedback'] ?></span>
    </div>
  </div>

  <div class="metric-card">
    <div class="metric-icon-wrap metric-icon-resolved"><?= icon('star-filled', '', 22) ?></div>
    <div class="metric-content">
      <span class="metric-label">5-Star Ratings</span>
      <span class="metric-number"><?= (int)$stats['stars_5'] ?></span>
    </div>
  </div>

  <div class="metric-card">
    <div class="metric-icon-wrap metric-icon-pending"><?= icon('thumbs-up', '', 22) ?></div>
    <div class="metric-content">
      <span class="metric-label">4-Star Ratings</span>
      <span class="metric-number"><?= (int)$stats['stars_4'] ?></span>
    </div>
  </div>
</div>

<!-- Feedback Entries List (Figure 13) -->
<div class="card" style="max-width: 1000px;">
  <div class="card-header">
    <h2 class="card-title">Student Evaluations & Suggestions</h2>
  </div>
  <div class="card-body" style="display: flex; flex-direction: column; gap: 16px;">
    <?php if (empty($allFeedback)): ?>
      <div style="text-align: center; padding: 40px; color: var(--text-muted); display: flex; align-items: center; justify-content: center; gap: 8px;">
        <?= icon('message-square', '', 20) ?>
        <span>No student feedback submitted yet.</span>
      </div>
    <?php else: ?>
      <?php foreach ($allFeedback as $fb): ?>
        <div style="padding: 18px 22px; border: 1px solid var(--border-color); border-radius: var(--radius-md); background: #ffffff; box-shadow: var(--shadow-xs);">
          <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 8px;">
            <div>
              <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--navy-primary);"><?= e($fb['subject']) ?></h3>
                <span class="badge-tag" style="background: #e0e7ff; color: #3730a3;"><?= e($fb['feedback_type']) ?></span>
              </div>
              <div class="text-xs text-muted" style="margin-top: 2px;">
                By: <strong><?= e($fb['student_name']) ?></strong> (<?= e($fb['student_email']) ?>) <?= $fb['student_no'] ? '• ' . e($fb['student_no']) : '' ?>
              </div>
            </div>

            <div>
              <?= renderStarRating((int)$fb['rating']) ?>
            </div>
          </div>

          <p style="font-size: 0.925rem; color: var(--text-secondary); line-height: 1.6; margin-bottom: 8px;">
            <?= nl2br(e($fb['message'])) ?>
          </p>

          <span class="text-xs text-muted">Submitted on <?= formatDateTime($fb['submitted_at']) ?></span>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
