<?php
// student/submit_concern.php - Concern Submission Form (Figure 4 of Prototype)
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireStudent();
$user = getCurrentUser();

// Fetch categories
$categories = $pdo->query("SELECT * FROM categories ORDER BY category_id ASC")->fetchAll();

$pageTitle = "Submit Concern | ICMS-DPT-RRT";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="page-header" style="max-width: 800px; margin: 0 auto 24px auto;">
  <div class="page-title-wrap">
    <h1 class="page-title">Submit a Concern</h1>
    <p class="page-desc">Fill out the form below to submit your concern.</p>
  </div>
</div>

<div class="card" style="max-width: 800px;">
  <div class="card-body" style="padding: 28px;">
    <form action="../actions/concern_action.php" method="POST" enctype="multipart/form-data" id="submitConcernForm">
      <input type="hidden" name="action" value="create">

      <!-- Category (Figure 4) -->
      <div class="form-group">
        <label class="form-label" for="concernCategory">Category <span class="required">*</span></label>
        <select name="category_id" id="concernCategory" class="form-control" required>
          <option value="" disabled selected>-- Select Category --</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['category_id'] ?>"><?= e($cat['category_name']) ?> - <?= e($cat['description']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Concern Title (Figure 4) -->
      <div class="form-group">
        <label class="form-label" for="concernTitle">Concern Title <span class="required">*</span></label>
        <input type="text" name="title" id="concernTitle" class="form-control" placeholder="Enter a short title (e.g., Broken chairs in classroom Lab 2)" required>
      </div>

      <!-- Concern Description (Figure 4) -->
      <div class="form-group">
        <label class="form-label" for="concernDescription">Concern Description <span class="required">*</span></label>
        <textarea name="description" id="concernDescription" class="form-control" rows="5" placeholder="Please describe your concern in detail..." required></textarea>
        <span class="form-help">Be as specific as possible. The AI engine analyzes the keywords to determine urgency and target SLA resolution time.</span>
      </div>

      <!-- AI Dynamic Prioritization Live Preview Engine (DPT-RRT) -->
      <div class="ai-preview-box" id="aiPreviewBox">
        <div class="ai-preview-header">
          <span style="display: flex; align-items: center; gap: 6px;">
            <?= icon('zap', '', 18) ?>
            <span>AI Dynamic Prioritization Preview (DPT-RRT)</span>
          </span>
          <div style="display: flex; align-items: center; gap: 8px;">
            <span id="aiPriorityBadge" class="priority-pill priority-low">Low</span>
            <span id="aiScoreBadge" style="font-weight: 800; font-size: 0.8rem; color: #1e3a8a;">25/100</span>
          </div>
        </div>

        <div class="ai-score-bar-track">
          <div class="ai-score-bar-fill" id="aiScoreFill" style="width: 25%;"></div>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
          <span id="aiSlaBadge" style="font-size: 0.775rem; font-weight: 700; color: #2563eb;">Resolution Target SLA: 120 Hours (5 Days)</span>
          <span style="font-size: 0.75rem; color: #64748b;">Dynamic Score & SLA</span>
        </div>

        <ul id="aiReasonsList" style="margin-left: 18px; font-size: 0.775rem; color: #475569; margin-top: 4px;">
          <li>Awaiting input keywords...</li>
        </ul>

        <div id="aiRrtNotice" style="display: none; align-items: center; gap: 8px; background: #fee2e2; border: 1px solid #fca5a5; padding: 6px 12px; border-radius: var(--radius-sm); color: #991b1b; font-size: 0.775rem; font-weight: 700; margin-top: 4px;">
          <?= icon('alert-triangle', '', 18) ?>
          <span>RAPID RESPONSE TEAM (RRT) ALERT TRIGGERED: Immediate institutional escalation upon submission.</span>
        </div>
      </div>

      <!-- Attachment (Figure 4) -->
      <div class="form-group" style="margin-top: 20px;">
        <label class="form-label" for="attachment">Attachment (Optional)</label>
        <input type="file" name="attachment" id="attachment" class="form-control" accept=".jpg,.jpeg,.png,.pdf,.docx">
        <span class="form-help">Supported files: JPG, PNG, PDF, DOCX (Max 5MB). Helpful for facility defects or documentation evidence.</span>
      </div>

      <div style="margin-top: 24px;">
        <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
          <span>Submit Concern</span>
        </button>
      </div>
    </form>
  </div>
</div>

<script src="../assets/js/ai_preview.js"></script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
