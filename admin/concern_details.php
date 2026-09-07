<?php
// admin/concern_details.php - Admin Concern Review & Response Center
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireAdmin();
$concernId = (int)($_GET['id'] ?? 0);

if ($concernId <= 0) {
    header('Location: concerns.php');
    exit;
}

// Fetch concern with student details
$stmt = $pdo->prepare("
    SELECT c.*, s.name AS student_name, s.student_no, s.email AS student_email, s.department AS student_dept, s.program,
           cat.category_name, st.status_name
    FROM concerns c
    JOIN students s ON c.student_id = s.student_id
    JOIN categories cat ON c.category_id = cat.category_id
    JOIN statuses st ON c.status_id = st.status_id
    WHERE c.concern_id = :id
");
$stmt->execute(['id' => $concernId]);
$concern = $stmt->fetch();

if (!$concern) {
    setFlash('error', 'Concern ticket not found.');
    header('Location: concerns.php');
    exit;
}

// Fetch statuses
$statuses = $pdo->query("SELECT * FROM statuses ORDER BY status_id ASC")->fetchAll();

// Fetch timeline responses
$respStmt = $pdo->prepare("
    SELECT r.*, a.name AS admin_name, s.status_name
    FROM responses r
    JOIN administrators a ON r.admin_id = a.admin_id
    JOIN statuses s ON r.status_id = s.status_id
    WHERE r.concern_id = :id
    ORDER BY r.date_responded ASC
");
$respStmt->execute(['id' => $concernId]);
$responses = $respStmt->fetchAll();

$pageTitle = "Review Ticket {$concern['ticket_id']} | Admin ICMS-DPT-RRT";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<a href="concerns.php" class="back-link" style="display: inline-flex; align-items: center; gap: 6px;"><?= icon('arrow-left', '', 16) ?> Back to Manage Concerns</a>

<!-- Status Progress Bar -->
<?php
$currentStatus = $concern['status_name'];
$isPending    = ($currentStatus === 'Pending');
$isInProgress = ($currentStatus === 'In Progress');
$isResolved   = ($currentStatus === 'Resolved');
?>
<div class="card" style="margin-bottom: 24px;">
  <div class="card-body" style="padding: 16px 24px;">
    <div class="status-tracker">
      <div class="status-step <?= ($isPending ? 'current' : 'completed') ?>">
        <div class="status-step-icon">1</div>
        <span class="status-step-label">Pending</span>
      </div>
      <div class="status-step <?= ($isInProgress ? 'current' : ($isResolved ? 'completed' : '')) ?>">
        <div class="status-step-icon">2</div>
        <span class="status-step-label">In Progress</span>
      </div>
      <div class="status-step <?= ($isResolved ? 'completed current' : '') ?>">
        <div class="status-step-icon">3</div>
        <span class="status-step-label">Resolved</span>
      </div>
    </div>
  </div>
</div>

<!-- Main Details Card -->
<div class="card">
  <div class="card-header">
    <div style="display: flex; align-items: center; gap: 10px;">
      <h2 class="card-title">Ticket Review: <?= e($concern['ticket_id']) ?></h2>
      <?= renderStatusBadge($concern['status_name']) ?>
      <?= renderPriorityBadge($concern['priority'], $concern['ai_urgency_score']) ?>
    </div>
    <span class="text-xs text-muted">Submitted on <?= formatDateTime($concern['date_submitted']) ?></span>
  </div>

  <div class="card-body">
    <!-- Concern & Student Metadata Grid -->
    <div class="concern-meta-grid">
      <div class="meta-field">
        <span class="meta-label">Submitted By</span>
        <span class="meta-value"><?= e($concern['student_name']) ?></span>
        <span class="text-xs text-muted"><?= e($concern['student_no']) ?></span>
      </div>

      <div class="meta-field">
        <span class="meta-label">Program & College</span>
        <span class="meta-value"><?= e($concern['program']) ?></span>
        <span class="text-xs text-muted"><?= e($concern['student_dept']) ?></span>
      </div>

      <div class="meta-field">
        <span class="meta-label">Category</span>
        <span class="meta-value"><?= e($concern['category_name']) ?></span>
      </div>

      <div class="meta-field">
        <span class="meta-label">Target Resolution SLA</span>
        <span class="meta-value" style="color: #2563eb; font-weight: 700;"><?= (int)$concern['sla_hours'] ?> Hours</span>
      </div>
    </div>

    <!-- Description -->
    <div class="concern-description-block">
      <div class="concern-description-title">Concern Title & Description</div>
      <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 8px; color: var(--navy-primary);"><?= e($concern['title']) ?></h3>
      <div class="concern-description-text"><?= nl2br(e($concern['description'])) ?></div>

      <?php if (!empty($concern['attachment_path'])): ?>
        <?php 
          $attExt = strtolower(pathinfo($concern['attachment_path'], PATHINFO_EXTENSION));
          $isImage = in_array($attExt, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
        ?>
        <div style="margin-top: 18px; padding-top: 14px; border-top: 1px dashed var(--border-color);">
          <span class="meta-label" style="display: block; margin-bottom: 8px;">Attachment / Photographic Evidence</span>
          <?php if ($isImage): ?>
            <div style="max-width: 480px; margin-bottom: 12px; border-radius: var(--radius-md); overflow: hidden; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); background: #f8fafc;">
              <a href="../<?= e($concern['attachment_path']) ?>" target="_blank" title="Click to view full image in new tab">
                <img src="../<?= e($concern['attachment_path']) ?>" alt="Concern Evidence Photo" style="width: 100%; max-height: 280px; object-fit: cover; display: block; transition: opacity 0.2s ease;">
              </a>
            </div>
          <?php endif; ?>
          <a href="../<?= e($concern['attachment_path']) ?>" target="_blank" class="attachment-preview-card" style="display: inline-flex;">
            <?= icon($isImage ? 'camera' : 'paperclip', '', 20) ?>
            <span><?= e(basename($concern['attachment_path'])) ?></span>
            <span class="text-xs text-muted">(Click to view/download)</span>
          </a>
        </div>
      <?php endif; ?>
    </div>

    <!-- Prior Responses Timeline -->
    <div style="margin-top: 28px;">
      <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 16px; color: var(--navy-primary);">
        Audit Trail & Responses History (<?= count($responses) ?>)
      </h3>

      <?php if (empty($responses)): ?>
        <div style="background: #f8fafc; border: 1px dashed var(--border-color); border-radius: var(--radius-md); padding: 20px; text-align: center; color: var(--text-muted); margin-bottom: 24px;">
          No administrative response logged yet. Use the form below to respond.
        </div>
      <?php else: ?>
        <div class="timeline" style="margin-bottom: 32px;">
          <?php foreach ($responses as $resp): ?>
            <div class="timeline-item">
              <div class="timeline-dot"></div>
              <div class="timeline-box">
                <div class="timeline-header">
                  <div style="display: flex; align-items: center; gap: 10px;">
                    <span class="timeline-author" style="display: inline-flex; align-items: center; gap: 6px;"><?= icon('user', '', 16) ?> <?= e($resp['admin_name']) ?></span>
                    <?php if (!empty($resp['department_endorsed'])): ?>
                      <span class="timeline-department">Endorsed to: <?= e($resp['department_endorsed']) ?></span>
                    <?php endif; ?>
                  </div>
                  <div style="display: flex; align-items: center; gap: 8px;">
                    <span class="timeline-time"><?= formatDateTime($resp['date_responded']) ?></span>
                    <?= renderStatusBadge($resp['status_name']) ?>
                  </div>
                </div>
                <div class="timeline-content">
                  <?= nl2br(e($resp['response_text'])) ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Official Administrator Response Form (Anchor for #responseBox) -->
    <div id="responseBox" class="card" style="border: 2px solid var(--brand-blue-border); background-color: #fbfcfe; margin-top: 24px;">
      <div class="card-header" style="background-color: var(--brand-blue-light);">
        <h3 class="card-title" style="color: var(--brand-blue); display: flex; align-items: center; gap: 8px;">
          <?= icon('edit', '', 20) ?>
          <span>Submit Official Response & Update Status</span>
        </h3>
      </div>
      <div class="card-body">
        <form action="../actions/response_action.php" method="POST">
          <input type="hidden" name="concern_id" value="<?= $concern['concern_id'] ?>">

          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 16px;">
            <!-- Select Status -->
            <div class="form-group" style="margin-bottom: 0;">
              <label class="form-label" for="respStatus">Update Status <span class="required">*</span></label>
              <select name="status_id" id="respStatus" class="form-control" required>
                <?php foreach ($statuses as $st): ?>
                  <option value="<?= $st['status_id'] ?>" <?= ($concern['status_id'] == $st['status_id']) ? 'selected' : '' ?>>
                    <?= e($st['status_name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Endorse Department -->
            <div class="form-group" style="margin-bottom: 0;">
              <label class="form-label" for="respDept">Endorse to Department</label>
              <select name="department_endorsed" id="respDept" class="form-control">
                <option value="">-- Direct Resolution (No Endorsement) --</option>
                <option value="Facilities & Maintenance" <?= ($concern['category_name'] === 'Facilities') ? 'selected' : '' ?>>Facilities & Maintenance</option>
                <option value="College of Computing & Information Sciences (CCIS)">CCIS Dean Office</option>
                <option value="Registrar / Academic Affairs" <?= ($concern['category_name'] === 'Academic') ? 'selected' : '' ?>>Registrar / Academic Affairs</option>
                <option value="MIS / IT Services" <?= ($concern['category_name'] === 'Services') ? 'selected' : '' ?>>MIS / IT Services</option>
                <option value="Guidance & Student Welfare" <?= ($concern['category_name'] === 'Student Welfare') ? 'selected' : '' ?>>Guidance & Student Welfare</option>
              </select>
            </div>
          </div>

          <!-- 1-Click Canned Response Suggestions -->
          <div style="margin-bottom: 12px;">
            <span class="text-xs text-muted" style="font-weight: 700; text-transform: uppercase;">Quick Response Templates:</span>
            <div style="display: flex; gap: 6px; flex-wrap: wrap; margin-top: 4px;">
              <button type="button" class="btn btn-secondary btn-sm" onclick="setResponse('We have received and acknowledged your report. This concern has been officially endorsed to the maintenance team for inspection.')">
                <?= icon('wrench', '', 14) ?> <span>Endorse to Maintenance</span>
              </button>
              <button type="button" class="btn btn-secondary btn-sm" onclick="setResponse('Investigation is currently ongoing with the department head. We will update you with the resolution within 48 hours.')">
                <?= icon('search', '', 14) ?> <span>Investigation In Progress</span>
              </button>
              <button type="button" class="btn btn-secondary btn-sm" onclick="setResponse('This issue has been thoroughly resolved and inspected on-site. Thank you for bringing this to our attention!')">
                <?= icon('check-circle', '', 14) ?> <span>Mark as Fully Resolved</span>
              </button>
            </div>
          </div>

          <!-- Response Textarea -->
          <div class="form-group">
            <label class="form-label" for="responseText">Official Administrator Response <span class="required">*</span></label>
            <textarea name="response_text" id="responseText" class="form-control" rows="4" placeholder="Enter your response, action taken, and endorsement details..." required></textarea>
            <span class="form-help">This response will be visible on the student's status tracking timeline immediately.</span>
          </div>

          <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 16px;">
            <button type="submit" class="btn btn-primary btn-lg">
              <span>Publish Response & Update Status</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  function setResponse(text) {
    document.getElementById('responseText').value = text;
  }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
