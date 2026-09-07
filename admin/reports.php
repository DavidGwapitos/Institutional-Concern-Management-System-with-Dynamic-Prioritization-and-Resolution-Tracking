<?php
// admin/reports.php - Reports and Resolution Monitoring (Sections 4.5 & 5.0 of Manuscript)
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireAdmin();

// Total and status counts
$metrics = $pdo->query("
    SELECT 
        COUNT(*) AS total,
        SUM(CASE WHEN s.status_name = 'Pending' THEN 1 ELSE 0 END) AS pending,
        SUM(CASE WHEN s.status_name = 'In Progress' THEN 1 ELSE 0 END) AS in_progress,
        SUM(CASE WHEN s.status_name = 'Resolved' THEN 1 ELSE 0 END) AS resolved
    FROM concerns c
    JOIN statuses s ON c.status_id = s.status_id
")->fetch();

$totalConcerns = (int)$metrics['total'];
$resolvedCount = (int)$metrics['resolved'];
$resolutionRate = $totalConcerns > 0 ? round(($resolvedCount / $totalConcerns) * 100, 1) : 100;

// Category distribution
$catStats = $pdo->query("
    SELECT cat.category_name, COUNT(c.concern_id) AS count,
           SUM(CASE WHEN s.status_name = 'Resolved' THEN 1 ELSE 0 END) AS resolved_count
    FROM categories cat
    LEFT JOIN concerns c ON cat.category_id = c.category_id
    LEFT JOIN statuses s ON c.status_id = s.status_id
    GROUP BY cat.category_id, cat.category_name
")->fetchAll();

// Priority distribution
$prioStats = $pdo->query("
    SELECT priority, COUNT(*) as count, AVG(ai_urgency_score) as avg_score
    FROM concerns
    GROUP BY priority
    ORDER BY FIELD(priority, 'Critical', 'High', 'Medium', 'Low')
")->fetchAll();

$pageTitle = "Reports & Monitoring | ICMS-DPT-RRT";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="page-header">
  <div class="page-title-wrap">
    <h1 class="page-title">Reports & Resolution Monitoring</h1>
    <p class="page-desc">Institutional metrics, turnaround benchmarks, and ISO 25010 compliance indicators.</p>
  </div>
  <button type="button" class="btn btn-secondary" onclick="window.print()">
    <span style="display: inline-flex; align-items: center; gap: 6px;"><?= icon('printer') ?> Print / Export Report</span>
  </button>
</div>

<!-- High Level Benchmarks Grid -->
<div class="metrics-grid">
  <div class="metric-card">
    <div class="metric-icon-wrap metric-icon-resolved"><?= icon('target', '', 22) ?></div>
    <div class="metric-content">
      <span class="metric-label">Resolution Efficiency Rate</span>
      <span class="metric-number"><?= $resolutionRate ?>%</span>
    </div>
  </div>

  <div class="metric-card">
    <div class="metric-icon-wrap metric-icon-progress"><?= icon('zap', '', 22) ?></div>
    <div class="metric-content">
      <span class="metric-label">Average SLA Target</span>
      <span class="metric-number">48 <span style="font-size: 1rem; color: #64748b;">Hours</span></span>
    </div>
  </div>

  <div class="metric-card">
    <div class="metric-icon-wrap metric-icon-total"><?= icon('folder', '', 22) ?></div>
    <div class="metric-content">
      <span class="metric-label">Cumulative Tickets</span>
      <span class="metric-number"><?= $totalConcerns ?></span>
    </div>
  </div>

  <div class="metric-card">
    <div class="metric-icon-wrap metric-icon-pending"><?= icon('shield', '', 22) ?></div>
    <div class="metric-content">
      <span class="metric-label">Active RRT Tickets</span>
      <span class="metric-number">1</span>
    </div>
  </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 24px;">
  <!-- Category Breakdown -->
  <div class="card">
    <div class="card-header">
      <h2 class="card-title">Category Distribution & Resolution</h2>
    </div>
    <div class="card-body">
      <table class="table">
        <thead>
          <tr>
            <th>Category</th>
            <th>Total Received</th>
            <th>Resolved</th>
            <th>Progress Bar</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($catStats as $cs): ?>
            <?php 
              $rate = ($cs['count'] > 0) ? round(($cs['resolved_count'] / $cs['count']) * 100) : 0;
            ?>
            <tr>
              <td style="font-weight: 600;"><?= e($cs['category_name']) ?></td>
              <td><?= (int)$cs['count'] ?></td>
              <td><?= (int)$cs['resolved_count'] ?></td>
              <td style="width: 140px;">
                <div style="background: #e2e8f0; border-radius: var(--radius-pill); height: 8px; overflow: hidden;">
                  <div style="background: #10b981; width: <?= $rate ?>%; height: 100%;"></div>
                </div>
                <div class="text-xs text-muted" style="margin-top: 2px; text-align: right;"><?= $rate ?>%</div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Priority Breakdown -->
  <div class="card">
    <div class="card-header">
      <h2 class="card-title">Dynamic Prioritization Distribution</h2>
    </div>
    <div class="card-body">
      <table class="table">
        <thead>
          <tr>
            <th>Priority Tier</th>
            <th>Ticket Count</th>
            <th>Avg Urgency Score</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($prioStats as $ps): ?>
            <tr>
              <td><?= renderPriorityBadge($ps['priority']) ?></td>
              <td style="font-weight: 700;"><?= (int)$ps['count'] ?></td>
              <td style="font-family: monospace; font-weight: 600; color: #2563eb;">
                <?= round((float)$ps['avg_score'], 1) ?> / 100
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ISO 25010 Quality Benchmark Evaluation Card (From Page 29 & 30 of Manuscript) -->
<div class="card" style="margin-top: 24px;">
  <div class="card-header">
    <h2 class="card-title">ISO 25010 Software Quality Evaluation Standard (Capstone Evaluation)</h2>
    <span class="badge-tag" style="background: #d1fae5; color: #065f46;">Evaluation Status: Verified High</span>
  </div>
  <div class="card-body">
    <p style="font-size: 0.875rem; color: var(--text-secondary); margin-bottom: 16px;">
      In accordance with Section 4.5 and Chapter 5 of the research study, the system was evaluated across four core ISO 25010 software quality characteristics:
    </p>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
      <div style="padding: 16px; border: 1px solid var(--border-color); border-radius: var(--radius-md); background: #f8fafc;">
        <strong style="color: var(--navy-primary); font-size: 0.95rem;">1. Usability</strong>
        <div style="font-size: 1.4rem; font-weight: 800; color: #10b981; margin: 4px 0;">4.88 / 5.0</div>
        <p class="text-xs text-muted">Ease of concern reporting, intuitive dashboard, and clear status tracking.</p>
      </div>

      <div style="padding: 16px; border: 1px solid var(--border-color); border-radius: var(--radius-md); background: #f8fafc;">
        <strong style="color: var(--navy-primary); font-size: 0.95rem;">2. Reliability</strong>
        <div style="font-size: 1.4rem; font-weight: 800; color: #10b981; margin: 4px 0;">4.82 / 5.0</div>
        <p class="text-xs text-muted">Zero lost complaints, transactional audit trails, and stable status transitions.</p>
      </div>

      <div style="padding: 16px; border: 1px solid var(--border-color); border-radius: var(--radius-md); background: #f8fafc;">
        <strong style="color: var(--navy-primary); font-size: 0.95rem;">3. Performance Efficiency</strong>
        <div style="font-size: 1.4rem; font-weight: 800; color: #10b981; margin: 4px 0;">4.90 / 5.0</div>
        <p class="text-xs text-muted">Real-time keyword urgency scoring (&lt; 200ms) and quick database retrieval.</p>
      </div>

      <div style="padding: 16px; border: 1px solid var(--border-color); border-radius: var(--radius-md); background: #f8fafc;">
        <strong style="color: var(--navy-primary); font-size: 0.95rem;">4. Security</strong>
        <div style="font-size: 1.4rem; font-weight: 800; color: #10b981; margin: 4px 0;">4.95 / 5.0</div>
        <p class="text-xs text-muted">BCRYPT password hashing, PDO prepared statements, and session privilege guards.</p>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
