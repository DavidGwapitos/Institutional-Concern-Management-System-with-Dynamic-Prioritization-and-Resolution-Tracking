<?php
// student/concern_details.php - Concern Details & Admin Response Timeline (Figure 6 of Prototype)
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireStudent();
$user = getCurrentUser();
$studentId = $user['id'];
$concernId = (int)($_GET['id'] ?? 0);

if ($concernId <= 0) {
    header('Location: my_concerns.php');
    exit;
}

// Fetch concern details (guaranteeing it belongs to this student)
$stmt = $pdo->prepare("
    SELECT c.*, cat.category_name, s.status_name 
    FROM concerns c
    JOIN categories cat ON c.category_id = cat.category_id
    JOIN statuses s ON c.status_id = s.status_id
    WHERE c.concern_id = :id AND c.student_id = :student_id
");
$stmt->execute(['id' => $concernId, 'student_id' => $studentId]);
$concern = $stmt->fetch();

if (!$concern) {
    setFlash('error', 'Concern not found or you do not have permission to view it.');
    header('Location: my_concerns.php');
    exit;
}

// Fetch all administrative responses/updates for this ticket
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

$pageTitle = "Ticket {$concern['ticket_id']} Details | ICMS-DPT-RRT";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<a href="my_concerns.php" class="back-link" style="display: inline-flex; align-items: center; gap: 6px;"><?= icon('arrow-left', '', 16) ?> Back to My Concerns</a>

<!-- Status Tracker Progress Bar -->
<?php
$currentStatus = $concern['status_name'];
$isPending    = ($currentStatus === 'Pending');
$isInProgress = ($currentStatus === 'In Progress');
$isResolved   = ($currentStatus === 'Resolved');
?>
<div class="card" style="margin-bottom: 24px;">
  <div class="card-body" style="padding: 16px 24px;">
    <div class="status-tracker">
      <!-- Step 1: Pending -->
      <div class="status-step <?= ($isPending ? 'current' : 'completed') ?>">
        <div class="status-step-icon">1</div>
        <span class="status-step-label">Pending</span>
      </div>

      <!-- Step 2: In Progress -->
      <div class="status-step <?= ($isInProgress ? 'current' : ($isResolved ? 'completed' : '')) ?>">
        <div class="status-step-icon">2</div>
        <span class="status-step-label">In Progress</span>
      </div>

      <!-- Step 3: Resolved -->
      <div class="status-step <?= ($isResolved ? 'completed current' : '') ?>">
        <div class="status-step-icon">3</div>
        <span class="status-step-label">Resolved</span>
      </div>
    </div>
  </div>
</div>

<!-- Concern Details Card (Figure 6) -->
<div class="card">
  <div class="card-header">
    <h2 class="card-title">Concern Details</h2>
    <div><?= renderStatusBadge($concern['status_name']) ?></div>
  </div>

  <div class="card-body">
    <!-- Metadata Grid -->
    <div class="concern-meta-grid">
      <div class="meta-field">
        <span class="meta-label">Ticket ID</span>
        <span class="meta-value ticket-badge" style="font-size: 0.95rem;"><?= e($concern['ticket_id']) ?></span>
      </div>

      <div class="meta-field">
        <span class="meta-label">Concern Title</span>
        <span class="meta-value"><?= e($concern['title']) ?></span>
      </div>

      <div class="meta-field">
        <span class="meta-label">Category</span>
        <span class="meta-value"><?= e($concern['category_name']) ?></span>
      </div>

      <div class="meta-field">
        <span class="meta-label">Date Submitted</span>
        <span class="meta-value"><?= formatDateTime($concern['date_submitted']) ?></span>
      </div>

      <div class="meta-field">
        <span class="meta-label">Dynamic Priority</span>
        <span class="meta-value"><?= renderPriorityBadge($concern['priority'], $concern['ai_urgency_score']) ?></span>
      </div>

      <div class="meta-field">
        <span class="meta-label">Target SLA</span>
        <span class="meta-value" style="color: #2563eb; font-weight: 700;"><?= (int)$concern['sla_hours'] ?> Hours</span>
      </div>
    </div>

    <!-- Description Block (Figure 6) -->
    <div class="concern-description-block">
      <div class="concern-description-title">Description</div>
      <div class="concern-description-text"><?= nl2br(e($concern['description'])) ?></div>

      <!-- Attachment Preview (Figure 6) -->
      <?php if (!empty($concern['attachment_path'])): ?>
        <?php 
          $attExt = strtolower(pathinfo($concern['attachment_path'], PATHINFO_EXTENSION));
          $isImage = in_array($attExt, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
        ?>
        <div style="margin-top: 18px; padding-top: 14px; border-top: 1px dashed var(--border-color);">
          <span class="meta-label" style="display: block; margin-bottom: 8px;">Attachment / Evidence</span>
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

    <!-- Updates / Admin Response Timeline (Figure 6) -->
    <div style="margin-top: 32px;">
      <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 16px; color: var(--navy-primary); display: flex; align-items: center; gap: 8px;">
        <span>Updates / Admin Response</span>
        <span class="badge-tag" style="background: #f1f5f9; color: #475569;"><?= count($responses) ?></span>
      </h3>

      <?php if (empty($responses)): ?>
        <div style="background: #f8fafc; border: 1px dashed var(--border-color); border-radius: var(--radius-md); padding: 24px; text-align: center; color: var(--text-muted); display: flex; align-items: center; justify-content: center; gap: 8px;">
          <?= icon('clock', '', 18) ?>
          <span>Your concern is currently pending review by the department administrator. Updates will appear here as soon as an action is taken.</span>
        </div>
      <?php else: ?>
        <div class="timeline">
          <?php foreach ($responses as $resp): ?>
            <div class="timeline-item">
              <div class="timeline-dot"></div>
              <div class="timeline-box">
                <div class="timeline-header">
                  <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
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
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
