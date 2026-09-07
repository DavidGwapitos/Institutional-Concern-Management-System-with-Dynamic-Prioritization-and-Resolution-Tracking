<?php
// student/feedback.php - Student Feedback Submission (Figure 3 of Prototype)
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireStudent();
$user = getCurrentUser();
$studentId = $user['id'];

// Fetch past feedback from this student
$stmt = $pdo->prepare("SELECT * FROM feedback WHERE student_id = :id ORDER BY submitted_at DESC");
$stmt->execute(['id' => $studentId]);
$pastFeedback = $stmt->fetchAll();

$pageTitle = "Student Feedback | ICMS-DPT-RRT";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="page-header" style="max-width: 760px; margin: 0 auto 24px auto;">
  <div class="page-title-wrap">
    <h1 class="page-title">Feedback</h1>
    <p class="page-desc">We value your feedback! Share your thoughts, suggestions, or report any issues about the system.</p>
  </div>
</div>

<div class="card" style="max-width: 760px;">
  <div class="card-body" style="padding: 28px;">
    <!-- Form (Figure 3) -->
    <form action="../actions/feedback_action.php" method="POST">
      <!-- Feedback Type -->
      <div class="form-group">
        <label class="form-label" for="feedbackType">Feedback Type <span class="required">*</span></label>
        <select name="feedback_type" id="feedbackType" class="form-control" required>
          <option value="" disabled selected>Select Feedback type</option>
          <option value="System Feedback">System Feedback & User Interface</option>
          <option value="Facilities Feedback">Facilities & Campus Environment</option>
          <option value="Academic Feedback">Academic Support & Curriculum</option>
          <option value="Services Feedback">Student Services & Administration</option>
          <option value="General Suggestion">General Suggestion</option>
        </select>
      </div>

      <!-- Subject -->
      <div class="form-group">
        <label class="form-label" for="subject">Subject <span class="required">*</span></label>
        <input type="text" name="subject" id="subject" class="form-control" placeholder="Enter subject" required>
      </div>

      <!-- Message -->
      <div class="form-group">
        <label class="form-label" for="message">Message <span class="required">*</span></label>
        <textarea name="message" id="message" class="form-control" rows="5" placeholder="Write your feedback here..." required></textarea>
      </div>

      <!-- 5-Star Rating (Figure 3 & 13) -->
      <div class="form-group">
        <label class="form-label">Rating</label>
        <input type="hidden" name="rating" id="ratingValue" value="5">
        <div class="star-rating" title="Click a star to rate">
          <span class="star selected" data-value="1"><?= icon('star') ?></span>
          <span class="star selected" data-value="2"><?= icon('star') ?></span>
          <span class="star selected" data-value="3"><?= icon('star') ?></span>
          <span class="star selected" data-value="4"><?= icon('star') ?></span>
          <span class="star selected" data-value="5"><?= icon('star') ?></span>
        </div>
        <span class="form-help">Rate your overall experience with the concern resolution services (1 to 5 stars).</span>
      </div>

      <div style="margin-top: 24px;">
        <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
          <span style="display: inline-flex; align-items: center; gap: 8px;"><?= icon('message-square') ?> Submit Feedback</span>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Past Feedback Submissions -->
<?php if (!empty($pastFeedback)): ?>
<div class="card" style="max-width: 760px; margin-top: 32px;">
  <div class="card-header">
    <h2 class="card-title">Your Submitted Feedback</h2>
  </div>
  <div class="card-body" style="display: flex; flex-direction: column; gap: 16px;">
    <?php foreach ($pastFeedback as $fb): ?>
      <div style="padding: 16px; border: 1px solid var(--border-color); border-radius: var(--radius-md); background: #f8fafc;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
          <div style="display: flex; align-items: center; gap: 8px;">
            <strong style="font-size: 0.95rem; color: var(--navy-primary);"><?= e($fb['subject']) ?></strong>
            <span class="badge-tag" style="background: #e0e7ff; color: #3730a3;"><?= e($fb['feedback_type']) ?></span>
          </div>
          <div>
            <?= renderStarRating((int)$fb['rating']) ?>
          </div>
        </div>
        <p style="font-size: 0.875rem; color: var(--text-secondary); line-height: 1.5; margin-bottom: 6px;">
          <?= nl2br(e($fb['message'])) ?>
        </p>
        <span class="text-xs text-muted"><?= formatDateTime($fb['submitted_at']) ?></span>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
