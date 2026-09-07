<?php
// admin/profile.php - Administrator Profile Management (Figure 14 of Prototype)
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireAdmin();
$user = getCurrentUser();
$adminId = $user['id'];

// Fetch latest admin record
$stmt = $pdo->prepare("SELECT * FROM administrators WHERE admin_id = :id");
$stmt->execute(['id' => $adminId]);
$admin = $stmt->fetch();

if (!$admin) {
    header('Location: dashboard.php');
    exit;
}

$pageTitle = "Admin Profile | ICMS-DPT-RRT";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="page-header" style="max-width: 960px; margin: 0 auto 24px auto;">
  <div class="page-title-wrap">
    <h1 class="page-title">Administrator Profile</h1>
    <p class="page-desc">View and manage your administrative account details.</p>
  </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px; max-width: 960px; margin: 0 auto;">
  <!-- Profile Overview Card (Figure 14) -->
  <div class="card">
    <div class="card-body" style="padding: 32px 24px; text-align: center;">
      <div style="position: relative; width: 96px; height: 96px; margin: 0 auto 20px auto;">
        <div style="width: 100%; height: 100%; border-radius: 50%; background: linear-gradient(135deg, #0e2246, #2563eb); color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: 800; box-shadow: var(--shadow-md);">
          <?= strtoupper(substr($admin['name'], 0, 1)) ?>
        </div>
        <div style="position: absolute; bottom: 0; right: 0; background: #0e2246; color: #ffffff; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid #ffffff;">
          <?= icon('shield', '', 14) ?>
        </div>
      </div>

      <h2 style="font-size: 1.35rem; font-weight: 700; color: var(--text-primary); margin-bottom: 4px;"><?= e($admin['name']) ?></h2>
      <span class="badge-tag" style="background: #f3e8ff; color: #7e22ce; font-size: 0.8rem; padding: 4px 12px;">System Administrator</span>

      <div style="margin-top: 28px; text-align: left; display: flex; flex-direction: column; gap: 14px; border-top: 1px solid var(--border-color); padding-top: 20px;">
        <div>
          <span class="text-xs text-muted" style="font-weight: 600; text-transform: uppercase;">Full Name</span>
          <div style="font-weight: 600; color: var(--text-primary);"><?= e($admin['name']) ?></div>
        </div>

        <div>
          <span class="text-xs text-muted" style="font-weight: 600; text-transform: uppercase;">Administrator Email</span>
          <div style="font-weight: 600; color: var(--text-primary);"><?= e($admin['email']) ?></div>
        </div>

        <div>
          <span class="text-xs text-muted" style="font-weight: 600; text-transform: uppercase;">Assigned Department</span>
          <div style="font-weight: 600; color: var(--text-primary);"><?= e($admin['department']) ?></div>
        </div>

        <div>
          <span class="text-xs text-muted" style="font-weight: 600; text-transform: uppercase;">Institutional Role</span>
          <div style="font-weight: 600; color: var(--brand-blue);"><?= e($admin['role']) ?></div>
        </div>

        <div>
          <span class="text-xs text-muted" style="font-weight: 600; text-transform: uppercase;">Account Created</span>
          <div style="font-weight: 600; color: var(--text-primary);"><?= formatDate($admin['created_at']) ?></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Profile & Credentials Card -->
  <div class="card">
    <div class="card-header">
      <h2 class="card-title">Update Administrator Credentials</h2>
    </div>
    <div class="card-body" style="padding: 24px;">
      <form action="../actions/profile_action.php" method="POST">
        <div class="form-group">
          <label class="form-label" for="adminName">Full Name <span class="required">*</span></label>
          <input type="text" name="name" id="adminName" class="form-control" value="<?= e($admin['name']) ?>" required>
        </div>

        <div class="form-group">
          <label class="form-label" for="adminEmail">Email Address (Read-only)</label>
          <input type="email" id="adminEmail" class="form-control" value="<?= e($admin['email']) ?>" readonly style="background: #f1f5f9; cursor: not-allowed;">
        </div>

        <div class="form-group">
          <label class="form-label">Department / Assignment</label>
          <input type="text" class="form-control" value="<?= e($admin['department']) ?>" readonly style="background: #f1f5f9; cursor: not-allowed;">
        </div>

        <div style="margin: 20px 0; border-top: 1px dashed var(--border-color); padding-top: 16px;">
          <h3 style="font-size: 0.95rem; font-weight: 700; color: var(--navy-primary); margin-bottom: 12px;">Change Administrator Password</h3>
          <div class="form-group">
            <label class="form-label" for="adminPassword">New Password</label>
            <input type="password" name="password" id="adminPassword" class="form-control" placeholder="Leave blank to keep existing password">
            <span class="form-help">Leave blank if you do not wish to modify the admin password.</span>
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
