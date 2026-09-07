<?php
// admin/announcements.php - Manage Announcements (Figure 12 of Prototype)
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireAdmin();

$announcements = $pdo->query("
    SELECT a.*, adm.name AS author_name 
    FROM announcements a
    LEFT JOIN administrators adm ON a.admin_id = adm.admin_id
    ORDER BY a.is_urgent DESC, a.published_at DESC
")->fetchAll();

$totalCount = count($announcements);

$pageTitle = "Announcements | ICMS-DPT-RRT";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="page-header" style="max-width: 960px; margin: 0 auto 24px auto;">
  <div class="page-title-wrap">
    <h1 class="page-title">Announcements</h1>
    <p class="page-desc">View important announcements.</p>
  </div>
  <button type="button" class="btn btn-primary" onclick="openModal('addAnnouncementModal')">
    <span style="display: inline-flex; align-items: center; gap: 6px;"><?= icon('plus') ?> New Announcement</span>
  </button>
</div>

<!-- Announcements List (Figure 12) -->
<div class="card" style="max-width: 960px;">
  <div class="card-body" style="padding: 24px; display: flex; flex-direction: column; gap: 20px;">
    <?php if (empty($announcements)): ?>
      <div style="text-align: center; padding: 40px; color: var(--text-muted); display: flex; align-items: center; justify-content: center; gap: 8px;">
        <?= icon('megaphone', '', 20) ?>
        <span>No announcements posted yet. Click <strong>"New Announcement"</strong> above to publish one.</span>
      </div>
    <?php else: ?>
      <?php foreach ($announcements as $anc): ?>
        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; padding-bottom: 20px; border-bottom: 1px solid var(--border-subtle);">
          <div style="display: flex; align-items: flex-start; gap: 16px;">
            <div style="width: 44px; height: 44px; border-radius: var(--radius-md); background: <?= $anc['is_urgent'] ? '#fee2e2' : '#eff6ff' ?>; color: <?= $anc['is_urgent'] ? '#dc2626' : '#2563eb' ?>; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <?= $anc['is_urgent'] ? icon('alert-triangle', '', 22) : icon('megaphone', '', 22) ?>
            </div>
            <div>
              <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 4px;">
                <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--text-primary);"><?= e($anc['title']) ?></h3>
                <?php if ($anc['is_urgent']): ?>
                  <span class="priority-pill priority-critical">Urgent</span>
                <?php endif; ?>
                <span class="badge-tag" style="background: #f1f5f9; color: #475569;"><?= e($anc['category']) ?></span>
              </div>
              <p style="font-size: 0.9rem; color: var(--text-secondary); line-height: 1.6; margin-bottom: 8px;">
                <?= nl2br(e($anc['content'])) ?>
              </p>
              <span class="text-xs text-muted" style="display: inline-flex; align-items: center; gap: 4px;"><?= icon('calendar', '', 14) ?> Published: <?= formatDate($anc['published_at']) ?></span>
            </div>
          </div>

          <div>
            <a href="../actions/announcement_action.php?action=delete&id=<?= $anc['announcement_id'] ?>" 
               class="btn-icon" 
               title="Delete Announcement" 
               data-confirm="Are you sure you want to delete announcement &lt;strong&gt;<?= e($anc['title']) ?>&lt;/strong&gt;?"
               data-confirm-title="Delete Announcement"
               data-confirm-text="Delete"
               data-confirm-type="danger"
               style="color: #dc2626;">
              <?= icon('trash') ?>
            </a>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
  <div class="card-footer">
    <span class="text-sm text-muted">Showing 1 to <?= $totalCount ?> of <?= $totalCount ?> entries</span>
  </div>
</div>

<!-- Modal: + New Announcement (Figure 12) -->
<div class="modal-backdrop" id="addAnnouncementModal" onclick="if(event.target === this) closeModal('addAnnouncementModal')">
  <div class="modal-content">
    <div class="modal-header">
      <h3 class="card-title" style="display: flex; align-items: center; gap: 8px;"><?= icon('megaphone') ?> <span>New Announcement</span></h3>
      <button type="button" class="alert-close" onclick="closeModal('addAnnouncementModal')">&times;</button>
    </div>
    <form action="../actions/announcement_action.php" method="POST">
      <input type="hidden" name="action" value="create">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label" for="ancTitle">Announcement Title <span class="required">*</span></label>
          <input type="text" name="title" id="ancTitle" class="form-control" placeholder="e.g. Scheduled Maintenance" required>
        </div>

        <div class="form-group">
          <label class="form-label" for="ancCategory">Category</label>
          <select name="category" id="ancCategory" class="form-control">
            <option value="General">General Notice</option>
            <option value="Maintenance">Maintenance & Facilities</option>
            <option value="Academic">Academic & Schedules</option>
            <option value="Services">Campus Services</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label" for="ancContent">Content / Announcement Details <span class="required">*</span></label>
          <textarea name="content" id="ancContent" class="form-control" rows="4" placeholder="Enter the full announcement text..." required></textarea>
        </div>

        <div class="form-group" style="flex-direction: row; align-items: center; gap: 8px;">
          <input type="checkbox" name="is_urgent" id="ancUrgent" value="1">
          <label for="ancUrgent" style="font-size: 0.875rem; font-weight: 600; color: #dc2626; cursor: pointer;">
            Mark as Urgent Bulletin (Highlights with red warning banner for all users)
          </label>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('addAnnouncementModal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Publish Announcement</button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
