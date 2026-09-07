<?php
// student/my_concerns.php - My Concerns List (Figure 5 of Prototype)
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireStudent();
$user = getCurrentUser();
$studentId = $user['id'];

// Fetch all concerns of this student
$stmt = $pdo->prepare("
    SELECT c.*, cat.category_name, s.status_name 
    FROM concerns c
    JOIN categories cat ON c.category_id = cat.category_id
    JOIN statuses s ON c.status_id = s.status_id
    WHERE c.student_id = :student_id
    ORDER BY c.date_submitted DESC
");
$stmt->execute(['student_id' => $studentId]);
$concerns = $stmt->fetchAll();
$totalEntries = count($concerns);

// Fetch categories for filtering
$categories = $pdo->query("SELECT * FROM categories ORDER BY category_name ASC")->fetchAll();

$pageTitle = "My Concerns | ICMS-DPT-RRT";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="page-header">
  <div class="page-title-wrap">
    <h1 class="page-title">My Concerns</h1>
    <p class="page-desc">Track the status of your submitted concerns.</p>
  </div>
  <a href="submit_concern.php" class="btn btn-primary">
    <span><?= icon('edit') ?> Submit Concern</span>
  </a>
</div>

<!-- Search & Filter Bar -->
<div class="filter-bar">
  <div class="search-input-wrap">
    <span class="search-icon"><?= icon('search', '', 16) ?></span>
    <input type="text" id="tableSearchInput" class="form-control" placeholder="Search your concerns...">
  </div>

  <div style="display: flex; gap: 10px; flex-wrap: wrap;">
    <select id="categoryFilter" class="form-control" style="width: auto; min-width: 150px;">
      <option value="all">All Categories</option>
      <?php foreach ($categories as $cat): ?>
        <option value="<?= e(strtolower($cat['category_name'])) ?>"><?= e($cat['category_name']) ?></option>
      <?php endforeach; ?>
    </select>

    <select id="statusFilter" class="form-control" style="width: auto; min-width: 140px;">
      <option value="all">All Statuses</option>
      <option value="pending">Pending</option>
      <option value="in progress">In Progress</option>
      <option value="resolved">Resolved</option>
    </select>
  </div>
</div>

<!-- My Concerns Table (Figure 5) -->
<div class="card">
  <div class="table-responsive">
    <table class="table" id="dataTable">
      <thead>
        <tr>
          <th>Ticket ID</th>
          <th>Concern Title</th>
          <th>Category</th>
          <th>Date Submitted</th>
          <th>Priority / SLA</th>
          <th>Status</th>
          <th style="text-align: right;">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($concerns)): ?>
          <tr>
            <td colspan="7" style="text-align: center; padding: 40px; color: var(--text-muted);">
              You have not submitted any concerns yet. Click <strong>"Submit Concern"</strong> to create one.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($concerns as $concern): ?>
            <tr data-category="<?= e(strtolower($concern['category_name'])) ?>" data-status="<?= e(strtolower($concern['status_name'])) ?>">
              <td>
                <span class="ticket-badge"><?= e($concern['ticket_id']) ?></span>
              </td>
              <td style="font-weight: 600;">
                <a href="concern_details.php?id=<?= $concern['concern_id'] ?>" style="color: var(--text-primary);">
                  <?= e($concern['title']) ?>
                </a>
              </td>
              <td><?= e($concern['category_name']) ?></td>
              <td class="text-muted text-sm"><?= formatDate($concern['date_submitted']) ?></td>
              <td>
                <?= renderPriorityBadge($concern['priority'], $concern['ai_urgency_score']) ?>
                <div class="text-xs text-muted" style="margin-top: 2px;">Target: <?= (int)$concern['sla_hours'] ?>h SLA</div>
              </td>
              <td><?= renderStatusBadge($concern['status_name']) ?></td>
              <td style="text-align: right;">
                <a href="concern_details.php?id=<?= $concern['concern_id'] ?>" class="btn-icon" title="View Details">
                  <?= icon('eye') ?>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
          <tr id="tableEmptyRow" style="display: none;">
            <td colspan="7" style="text-align: center; padding: 24px; color: var(--text-muted);">
              No concerns matching your search or filters.
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <div class="card-footer">
    <span class="text-sm text-muted">Showing 1 to <?= $totalEntries ?> of <?= $totalEntries ?> entries</span>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
