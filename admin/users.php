<?php
// admin/users.php - Manage Users (Figure 11 of Prototype)
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireAdmin();

// Fetch students and administrators
$students = $pdo->query("
    SELECT student_id AS id, name, email, 'Student' AS role, status, created_at, 'student' AS type
    FROM students
")->fetchAll();

$admins = $pdo->query("
    SELECT admin_id AS id, name, email, role, status, created_at, 'admin' AS type
    FROM administrators
")->fetchAll();

$allUsers = array_merge($students, $admins);
usort($allUsers, fn($a, $b) => strcmp($a['name'], $b['name']));
$totalUsers = count($allUsers);

$pageTitle = "Manage Users | ICMS-DPT-RRT";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="page-header">
  <div class="page-title-wrap">
    <h1 class="page-title">Manage Users</h1>
    <p class="page-desc">View and manage system users.</p>
  </div>
  <button type="button" class="btn btn-primary" onclick="openModal('addUserModal')">
    <span style="display: inline-flex; align-items: center; gap: 6px;"><?= icon('plus') ?> Add User</span>
  </button>
</div>

<!-- Users Table (Figure 11) -->
<div class="card">
  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th>Name</th>
          <th>Email</th>
          <th>Role</th>
          <th>Status</th>
          <th style="text-align: right;">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($allUsers as $u): ?>
          <tr>
            <td style="font-weight: 600;">
              <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: <?= ($u['type'] === 'admin') ? '#1e3a8a' : '#2563eb' ?>; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 700;">
                  <?= strtoupper(substr($u['name'], 0, 1)) ?>
                </div>
                <span><?= e($u['name']) ?></span>
              </div>
            </td>
            <td><?= e($u['email']) ?></td>
            <td>
              <span class="badge-tag" style="background: <?= ($u['type'] === 'admin') ? '#f3e8ff; color: #7e22ce;' : '#eff6ff; color: #1d4ed8;' ?>">
                <?= e($u['role']) ?>
              </span>
            </td>
            <td>
              <a href="../actions/user_action.php?action=toggle_status&type=<?= $u['type'] ?>&id=<?= $u['id'] ?>" 
                 title="Click to toggle status"
                 data-confirm="Are you sure you want to change the status of &lt;strong&gt;<?= e($u['name']) ?>&lt;/strong&gt; to <?= ($u['status'] === 'Active') ? 'Inactive' : 'Active' ?>?"
                 data-confirm-title="Change Account Status"
                 data-confirm-text="Change Status"
                 data-confirm-type="warning">
                <?php if ($u['status'] === 'Active'): ?>
                  <span class="badge-tag" style="background: #d1fae5; color: #065f46; cursor: pointer;">● Active</span>
                <?php else: ?>
                  <span class="badge-tag" style="background: #f1f5f9; color: #64748b; cursor: pointer;">○ Inactive</span>
                <?php endif; ?>
              </a>
            </td>
            <td style="text-align: right;">
              <div style="display: inline-flex; gap: 4px;">
                <a href="../actions/user_action.php?action=toggle_status&type=<?= $u['type'] ?>&id=<?= $u['id'] ?>" 
                   class="btn-icon" 
                   title="Toggle Status (Active/Inactive)"
                   data-confirm="Are you sure you want to change the status of &lt;strong&gt;<?= e($u['name']) ?>&lt;/strong&gt; to <?= ($u['status'] === 'Active') ? 'Inactive' : 'Active' ?>?"
                   data-confirm-title="Change Account Status"
                   data-confirm-text="Change Status"
                   data-confirm-type="warning">
                  <?= icon('refresh') ?>
                </a>
                <a href="../actions/user_action.php?action=delete&type=<?= $u['type'] ?>&id=<?= $u['id'] ?>" 
                   class="btn-icon" 
                   title="Delete User" 
                   data-confirm="Are you sure you want to permanently remove user &lt;strong&gt;<?= e($u['name']) ?>&lt;/strong&gt; from the system? This action cannot be undone."
                   data-confirm-title="Remove User Account"
                   data-confirm-text="Remove User"
                   data-confirm-type="danger"
                   style="color: #dc2626;">
                  <?= icon('trash') ?>
                </a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="card-footer">
    <span class="text-sm text-muted">Showing 1 to <?= $totalUsers ?> of <?= $totalUsers ?> entries</span>
  </div>
</div>

<!-- Modal: Add New User (Figure 11) -->
<div class="modal-backdrop" id="addUserModal" onclick="if(event.target === this) closeModal('addUserModal')">
  <div class="modal-content">
    <div class="modal-header">
      <h3 class="card-title">Add New User</h3>
      <button type="button" class="alert-close" onclick="closeModal('addUserModal')">&times;</button>
    </div>
    <form action="../actions/user_action.php" method="POST">
      <input type="hidden" name="action" value="add">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label" for="userRole">Account Role</label>
          <select name="role" id="userRole" class="form-control" onchange="toggleAddUserFields(this.value)">
            <option value="student" selected>Student</option>
            <option value="admin">Administrator</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label" for="addName">Full Name <span class="required">*</span></label>
          <input type="text" name="name" id="addName" class="form-control" placeholder="e.g. Maria Santos" required>
        </div>

        <div class="form-group">
          <label class="form-label" for="addEmail">Email Address <span class="required">*</span></label>
          <input type="email" name="email" id="addEmail" class="form-control" placeholder="e.g. user@snsu.edu.ph" required>
        </div>

        <div class="form-group">
          <label class="form-label" for="addPassword">Password</label>
          <input type="password" name="password" id="addPassword" class="form-control" value="student123" required>
          <span class="form-help">Default is student123. Can be changed upon login.</span>
        </div>

        <div id="studentOnlyFields">
          <div class="form-group">
            <label class="form-label" for="addStudentNo">Student Number</label>
            <input type="text" name="student_no" id="addStudentNo" class="form-control" placeholder="e.g. SNSU-2024-05512">
          </div>
          <div class="form-group">
            <label class="form-label" for="addProgram">Degree Program</label>
            <input type="text" name="program" id="addProgram" class="form-control" value="BS Information Technology">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="addDepartment">Department / College</label>
          <input type="text" name="department" id="addDepartment" class="form-control" value="College of Computing & Information Sciences">
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('addUserModal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Create User</button>
      </div>
    </form>
  </div>
</div>

<script>
  function toggleAddUserFields(role) {
    const studentFields = document.getElementById('studentOnlyFields');
    studentFields.style.display = (role === 'student') ? 'block' : 'none';
  }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
