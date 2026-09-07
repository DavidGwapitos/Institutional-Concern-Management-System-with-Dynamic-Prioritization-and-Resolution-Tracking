<?php
// student/profile.php - Student Profile View & Edit (Figure 8 of Prototype)
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireStudent();
$user = getCurrentUser();
$studentId = $user['id'];

// Fetch latest student record
$stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = :id");
$stmt->execute(['id' => $studentId]);
$student = $stmt->fetch();

if (!$student) {
    header('Location: dashboard.php');
    exit;
}

$pageTitle = "My Profile | ICMS-DPT-RRT";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="page-header" style="max-width: 960px; margin: 0 auto 24px auto;">
  <div class="page-title-wrap">
    <h1 class="page-title">Profile</h1>
    <p class="page-desc">View and manage your profile information.</p>
  </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px; max-width: 960px; margin: 0 auto;">
  <!-- Profile Overview Card (Figure 8) -->
  <div class="card">
    <div class="card-body" style="padding: 32px 24px; text-align: center;">
      <div style="position: relative; width: 96px; height: 96px; margin: 0 auto 20px auto;">
        <div style="width: 100%; height: 100%; border-radius: 50%; background: linear-gradient(135deg, #2563eb, #1e3a8a); color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: 800; box-shadow: var(--shadow-md);">
          <?= strtoupper(substr($student['name'], 0, 1)) ?>
        </div>
        <div style="position: absolute; bottom: 0; right: 0; background: #2563eb; color: #ffffff; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid #ffffff;">
          <?= icon('camera', '', 14) ?>
        </div>
      </div>

      <h2 style="font-size: 1.35rem; font-weight: 700; color: var(--text-primary); margin-bottom: 4px;"><?= e($student['name']) ?></h2>
      <span class="badge-tag" style="background: #eff6ff; color: #1d4ed8; font-size: 0.8rem; padding: 4px 12px;">Student Account</span>

      <div style="margin-top: 28px; text-align: left; display: flex; flex-direction: column; gap: 14px; border-top: 1px solid var(--border-color); padding-top: 20px;">
        <div>
          <span class="text-xs text-muted" style="font-weight: 600; text-transform: uppercase;">Full Name</span>
          <div style="font-weight: 600; color: var(--text-primary);"><?= e($student['name']) ?></div>
        </div>

        <div>
          <span class="text-xs text-muted" style="font-weight: 600; text-transform: uppercase;">Student Number</span>
          <div style="font-family: monospace; font-weight: 700; color: var(--brand-blue);"><?= e($student['student_no']) ?></div>
        </div>

        <div>
          <span class="text-xs text-muted" style="font-weight: 600; text-transform: uppercase;">Institutional Email</span>
          <div style="font-weight: 600; color: var(--text-primary);"><?= e($student['email']) ?></div>
        </div>

        <div>
          <span class="text-xs text-muted" style="font-weight: 600; text-transform: uppercase;">College Department</span>
          <div style="font-weight: 600; color: var(--text-primary);"><?= e($student['department']) ?></div>
        </div>

        <div>
          <span class="text-xs text-muted" style="font-weight: 600; text-transform: uppercase;">Degree Program</span>
          <div style="font-weight: 600; color: var(--text-primary);"><?= e($student['program']) ?></div>
        </div>

        <div>
          <span class="text-xs text-muted" style="font-weight: 600; text-transform: uppercase;">Date Joined</span>
          <div style="font-weight: 600; color: var(--text-primary);"><?= formatDate($student['created_at']) ?></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Profile / Password Card -->
  <div class="card">
    <div class="card-header">
      <h2 class="card-title">Edit Profile Information</h2>
    </div>
    <div class="card-body" style="padding: 24px;">
      <form action="../actions/profile_action.php" method="POST">
        <div class="form-group">
          <label class="form-label" for="profileName">Full Name <span class="required">*</span></label>
          <input type="text" name="name" id="profileName" class="form-control" value="<?= e($student['name']) ?>" required>
        </div>

        <div class="form-group">
          <label class="form-label" for="profileEmail">Email Address (Read-only)</label>
          <input type="email" id="profileEmail" class="form-control" value="<?= e($student['email']) ?>" readonly style="background: #f1f5f9; cursor: not-allowed;">
          <span class="form-help">Institutional email is managed by the university registrar.</span>
        </div>

        <div class="form-group">
          <label class="form-label" for="profilePhone">Phone / Mobile Number</label>
          <input type="text" name="phone" id="profilePhone" class="form-control" value="<?= e($student['phone'] ?? '') ?>" placeholder="e.g. 09123456789">
        </div>

        <div style="margin: 20px 0; border-top: 1px dashed var(--border-color); padding-top: 16px;">
          <h3 style="font-size: 0.95rem; font-weight: 700; color: var(--navy-primary); margin-bottom: 12px;">Change Account Password</h3>
          <div class="form-group">
            <label class="form-label" for="profilePassword">New Password</label>
            <input type="password" name="password" id="profilePassword" class="form-control" placeholder="Leave blank to keep existing password">
            <span class="form-help">Leave blank if you do not wish to change your password.</span>
          </div>
        </div>

        <div style="margin-top: 24px;">
          <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
            <span>Save Profile Updates</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
