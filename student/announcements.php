<?php
// student/announcements.php - Student Announcements Feed (Figure 7 of Prototype)
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireStudent();

// Fetch announcements
$announcements = $pdo->query("
    SELECT a.*, adm.name AS author_name 
    FROM announcements a
    LEFT JOIN administrators adm ON a.admin_id = adm.admin_id
    ORDER BY a.is_urgent DESC, a.published_at DESC
")->fetchAll();

$pageTitle = "Announcements | ICMS-DPT-RRT";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="page-header" style="max-width: 900px; margin: 0 auto 24px auto;">
  <div class="page-title-wrap">
    <h1 class="page-title">Announcements</h1>
    <p class="page-desc">Stay updated with the latest announcements.</p>
  </div>
</div>

<div class="card" style="max-width: 900px;">
  <div class="card-body" style="padding: 24px; display: flex; flex-direction: column; gap: 20px;">
    <?php if (empty($announcements)): ?>
      <div style="text-align: center; padding: 40px; color: var(--text-muted); display: flex; align-items: center; justify-content: center; gap: 8px;">
        <?= icon('megaphone', '', 20) ?>
        <span>No campus announcements posted at this time.</span>
      </div>
    <?php else: ?>
      <?php foreach ($announcements as $anc): ?>
        <div style="display: flex; align-items: flex-start; gap: 18px; padding-bottom: 20px; border-bottom: 1px solid var(--border-subtle); position: relative;">
          <div style="width: 44px; height: 44px; border-radius: var(--radius-md); background: <?= $anc['is_urgent'] ? '#fee2e2' : '#eff6ff' ?>; color: <?= $anc['is_urgent'] ? '#dc2626' : '#2563eb' ?>; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <?= $anc['is_urgent'] ? icon('alert-triangle', '', 22) : icon('megaphone', '', 22) ?>
          </div>

          <div style="flex: 1;">
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 4px;">
              <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--text-primary);"><?= e($anc['title']) ?></h3>
              <?php if ($anc['is_urgent']): ?>
                <span class="priority-pill priority-critical">Urgent Bulletin</span>
              <?php endif; ?>
              <span class="badge-tag" style="background: #f1f5f9; color: #475569;"><?= e($anc['category']) ?></span>
            </div>

            <p style="font-size: 0.9rem; color: var(--text-secondary); line-height: 1.6; margin-bottom: 8px;">
              <?= nl2br(e($anc['content'])) ?>
            </p>

            <div style="display: flex; align-items: center; gap: 12px; font-size: 0.775rem; color: var(--text-muted);">
              <span style="display: inline-flex; align-items: center; gap: 4px;"><?= icon('calendar', '', 14) ?> Published: <?= formatDate($anc['published_at']) ?></span>
              <?php if (!empty($anc['author_name'])): ?>
                <span>• Posted by: <?= e($anc['author_name']) ?></span>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
