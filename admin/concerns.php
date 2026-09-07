<?php
// admin/concerns.php - Manage Concerns for Administrators (Figure 15 of Prototype)
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireAdmin();

// Handle ticket deletion if requested
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $delId = (int)$_GET['id'];
    $delStmt = $pdo->prepare("DELETE FROM concerns WHERE concern_id = :id");
    $delStmt->execute(['id' => $delId]);
    setFlash('success', 'Concern record deleted successfully.');
    header('Location: concerns.php');
    exit;
}

// Fetch categories for filtering
$categories = $pdo->query("SELECT * FROM categories ORDER BY category_name ASC")->fetchAll();

// Fetch all concerns with student name, category, and status
$concerns = $pdo->query("
    SELECT c.*, s.name AS student_name, cat.category_name, st.status_name
    FROM concerns c
    JOIN students s ON c.student_id = s.student_id
    JOIN categories cat ON c.category_id = cat.category_id
    JOIN statuses st ON c.status_id = st.status_id
    ORDER BY (c.priority = 'Critical' AND st.status_name != 'Resolved') DESC, c.date_submitted DESC
")->fetchAll();

$totalCount = count($concerns);

$pageTitle = "Manage Concerns | ICMS-DPT-RRT";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="page-header">
  <div class="page-title-wrap">
    <h1 class="page-title">Manage Concerns</h1>
    <p class="page-desc">View, update, and respond to student concerns.</p>
  </div>
</div>

<!-- Multi-Filter Bar (Figure 15) -->
<div class="filter-bar">
  <!-- Search Input (Figure 15) -->
  <div class="search-input-wrap">
    <span class="search-icon"><?= icon('search', '', 16) ?></span>
    <input type="text" id="tableSearchInput" class="form-control" placeholder="Search concerns, ticket ID, student name...">
  </div>

  <!-- All Categories (Figure 15) -->
  <select id="categoryFilter" class="form-control" style="width: auto; min-width: 150px;">
    <option value="all">All Categories</option>
    <?php foreach ($categories as $cat): ?>
      <option value="<?= e(strtolower($cat['category_name'])) ?>"><?= e($cat['category_name']) ?></option>
    <?php endforeach; ?>
  </select>

  <!-- All Statuses (Figure 15) -->
  <select id="statusFilter" class="form-control" style="width: auto; min-width: 140px;">
    <option value="all">All Statuses</option>
    <option value="pending">Pending</option>
    <option value="in progress">In Progress</option>
    <option value="resolved">Resolved</option>
  </select>

  <!-- Priority Filter -->
  <select id="priorityFilter" class="form-control" style="width: auto; min-width: 140px;" onchange="filterByPriority(this.value)">
    <option value="all">All Priorities</option>
    <option value="critical">Critical</option>
    <option value="high">High</option>
    <option value="medium">Medium</option>
    <option value="low">Low</option>
  </select>
</div>

<!-- Manage Concerns Table (Figure 15) -->
<div class="card">
  <div class="table-responsive">
    <table class="table" id="dataTable">
      <thead>
        <tr>
          <th>Ticket ID</th>
          <th>Title</th>
          <th>Category</th>
          <th>Submitted By</th>
          <th>Date Submitted</th>
          <th>Priority & SLA</th>
          <th>Status</th>
          <th style="text-align: right;">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($concerns as $concern): ?>
          <tr data-category="<?= e(strtolower($concern['category_name'])) ?>" 
              data-status="<?= e(strtolower($concern['status_name'])) ?>"
              data-priority="<?= e(strtolower($concern['priority'])) ?>">
            <td>
              <span class="ticket-badge"><?= e($concern['ticket_id']) ?></span>
              <?php if ($concern['is_rrt_alert'] && $concern['status_name'] !== 'Resolved'): ?>
                <span class="badge-tag" style="background: #fee2e2; color: #dc2626; font-size: 0.65rem;">RRT</span>
              <?php endif; ?>
            </td>
            <td style="font-weight: 600;">
              <a href="concern_details.php?id=<?= $concern['concern_id'] ?>" style="color: var(--text-primary);">
                <?= e($concern['title']) ?>
              </a>
            </td>
            <td><?= e($concern['category_name']) ?></td>
            <td><?= e($concern['student_name']) ?></td>
            <td class="text-muted text-sm"><?= formatDate($concern['date_submitted']) ?></td>
            <td>
              <?= renderPriorityBadge($concern['priority'], $concern['ai_urgency_score']) ?>
              <div class="text-xs text-muted" style="margin-top: 2px;">SLA: <?= (int)$concern['sla_hours'] ?>h</div>
            </td>
            <td><?= renderStatusBadge($concern['status_name']) ?></td>
            <td style="text-align: right;">
              <div style="display: inline-flex; gap: 4px;">
                <a href="concern_details.php?id=<?= $concern['concern_id'] ?>" class="btn-icon" title="View Details">
                  <?= icon('eye') ?>
                </a>
                <a href="concern_details.php?id=<?= $concern['concern_id'] ?>#responseBox" class="btn-icon" title="Respond & Endorse">
                  <?= icon('edit') ?>
                </a>
                <a href="concerns.php?action=delete&id=<?= $concern['concern_id'] ?>" 
                   class="btn-icon" 
                   title="Delete Concern" 
                   data-confirm="Are you sure you want to delete ticket &lt;strong&gt;<?= e($concern['ticket_id']) ?>&lt;/strong&gt;? This action cannot be undone."
                   data-confirm-title="Delete Concern Ticket"
                   data-confirm-text="Delete Ticket"
                   data-confirm-type="danger"
                   style="color: #dc2626;">
                  <?= icon('trash') ?>
                </a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        <tr id="tableEmptyRow" style="display: none;">
          <td colspan="8" style="text-align: center; padding: 28px; color: var(--text-muted);">
            No concerns matching your search or filters.
          </td>
        </tr>
      </tbody>
    </table>
  </div>
  
  <div class="card-footer">
    <span class="text-sm text-muted">Showing 1 to <?= $totalCount ?> of <?= $totalCount ?> entries</span>
    
    <!-- Pagination simulation matching Figure 15 (< 1 2 3 7 >) -->
    <div style="display: flex; gap: 4px; align-items: center;">
      <button type="button" class="btn btn-secondary btn-sm" disabled>&lt;</button>
      <button type="button" class="btn btn-primary btn-sm">1</button>
      <button type="button" class="btn btn-secondary btn-sm">2</button>
      <button type="button" class="btn btn-secondary btn-sm">3</button>
      <span style="padding: 0 4px; color: var(--text-muted);">...</span>
      <button type="button" class="btn btn-secondary btn-sm">7</button>
      <button type="button" class="btn btn-secondary btn-sm">&gt;</button>
    </div>
  </div>
</div>

<script>
  function filterByPriority(val) {
    const rows = document.querySelectorAll('#dataTable tbody tr:not(#tableEmptyRow)');
    const pVal = val.toLowerCase().trim();
    rows.forEach(row => {
      const p = (row.getAttribute('data-priority') || '').toLowerCase();
      if (pVal === 'all' || p === pVal) {
        row.style.display = '';
      } else {
        row.style.display = 'none';
      }
    });
  }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
