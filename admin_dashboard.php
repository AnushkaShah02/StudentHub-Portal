<?php
// admin_dashboard.php (Practical 14)
require_once __DIR__ . '/config/db.php';
check_session_timeout();

// RBAC: Restrict to Admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php?msg=Access denied: Administrative privileges required.");
    exit;
}

// 1. Fetch Aggregated Statistics
$studentCount = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$eventCount   = $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
$userCount    = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$avgAttendance= $pdo->query("SELECT ROUND(AVG(attendance), 1) FROM students")->fetchColumn();

// 2. Fetch Latest Audit Logs
$logs = $pdo->query("SELECT a.*, u.name as user_name FROM audit_logs a LEFT JOIN users u ON a.user_id = u.id ORDER BY a.id DESC LIMIT 6")->fetchAll();

// 3. Fetch Recent Registered Students
$recentStudents = $pdo->query("SELECT * FROM students ORDER BY id DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>StudentHub - Admin Command Center</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .stat-badge { font-size: 2.2rem; font-weight: 700; color: var(--primary); line-height: 1; }
    .table-container { width: 100%; border-collapse: collapse; margin-top: 15px; }
    .table-container th { text-align: left; padding: 12px; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); border-bottom: 2px solid var(--lavender); }
    .table-container td { padding: 12px; font-size: 0.88rem; border-bottom: 1px dashed var(--lavender); }
    .admin-nav { display: flex; gap: 10px; align-items: center; }
  </style>
</head>
<body>
  <div class="bg-blob-container">
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
  </div>

  <header class="global-nav">
    <div class="nav-container">
      <div class="logo-group">
        <div class="logo-icon"><i data-lucide="shield-alert"></i></div>
        <span class="logo-text">StudentHub Admin</span>
      </div>
      <nav class="nav-links">
        <a href="admin_dashboard.php" class="nav-item active"><i data-lucide="layout-dashboard" style="width:16px;"></i> Console</a>
        <a href="admin_students.php" class="nav-item"><i data-lucide="users" style="width:16px;"></i> Students CRUD</a>
        <a href="admin_events.php" class="nav-item"><i data-lucide="calendar" style="width:16px;"></i> Events CRUD</a>
        <a href="logout.php" class="nav-item" style="color: #FF5A5A;"><i data-lucide="log-out" style="width:16px;"></i> Logout</a>
      </nav>
    </div>
  </header>

  <main class="main-wrapper">
    <div class="page-intro" style="margin-bottom: 30px;">
      <div>
        <h1 class="font-poppins">System Administration & Monitoring</h1>
        <p style="color: var(--text-muted);">Real-time metrics, audit traceability, and access-controlled datasets.</p>
      </div>
    </div>

    <!-- Aggregations Grid -->
    <div class="grid-3" style="margin-bottom: 30px;">
      <article class="organic-card organic-card-1" style="padding: 24px;">
        <span style="font-size: 0.78rem; text-transform: uppercase; font-weight: 700; color: var(--text-muted);">Total Registered Students</span>
        <div class="stat-badge" style="margin-top: 10px;"><?= $studentCount; ?></div>
        <p style="font-size: 0.8rem; color: var(--success); margin-top: 8px;"><i data-lucide="check" style="width: 14px;"></i> Active Academic Cohort</p>
      </article>

      <article class="organic-card organic-card-2" style="padding: 24px;">
        <span style="font-size: 0.78rem; text-transform: uppercase; font-weight: 700; color: var(--text-muted);">Campus Events Hosted</span>
        <div class="stat-badge" style="margin-top: 10px; color: var(--warning);"><?= $eventCount; ?></div>
        <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 8px;">Hackathons & Symposiums</p>
      </article>

      <article class="organic-card organic-card-1" style="padding: 24px;">
        <span style="font-size: 0.78rem; text-transform: uppercase; font-weight: 700; color: var(--text-muted);">Overall Avg Attendance</span>
        <div class="stat-badge" style="margin-top: 10px; color: #4CAF50;"><?= $avgAttendance; ?>%</div>
        <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 8px;">Across all enrolled departments</p>
      </article>
    </div>

    <div class="grid-2">
      <!-- Recent Students -->
      <section class="organic-card organic-card-1" style="padding: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
          <h3 class="font-poppins" style="font-size: 1.1rem;"><i data-lucide="users" style="color: var(--primary);"></i> Recent Enrollees</h3>
          <a href="admin_students.php" style="font-size: 0.8rem; color: var(--primary); font-weight: 600;">Manage All &rarr;</a>
        </div>
        <table class="table-container">
          <thead>
            <tr><th>Enrollment</th><th>Name</th><th>Dept</th><th>CGPA</th></tr>
          </thead>
          <tbody>
            <?php foreach ($recentStudents as $s): ?>
            <tr>
              <td><strong><?= htmlspecialchars($s['student_id_num']); ?></strong></td>
              <td><?= htmlspecialchars($s['name']); ?></td>
              <td><?= htmlspecialchars($s['department']); ?></td>
              <td><span style="color: var(--primary); font-weight: 700;"><?= number_format($s['cgpa'], 2); ?></span></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </section>

      <!-- Practical 14 Audit Logs -->
      <section class="organic-card organic-card-2" style="padding: 24px;">
        <h3 class="font-poppins" style="font-size: 1.1rem; margin-bottom: 15px;"><i data-lucide="shield-check" style="color: var(--primary);"></i> Security Audit Trail</h3>
        <div style="display: flex; flex-direction: column; gap: 12px;">
          <?php foreach ($logs as $l): ?>
            <div style="padding: 10px; background: var(--bg-light); border-radius: var(--radius-organic-2); font-size: 0.82rem;">
              <div style="display: flex; justify-content: space-between; font-weight: 600;">
                <span style="color: var(--primary);"><?= htmlspecialchars($l['action']); ?></span>
                <span style="color: var(--text-muted); font-size: 0.72rem;"><?= $l['created_at']; ?></span>
              </div>
              <p style="color: var(--text-main); margin-top: 3px;"><?= htmlspecialchars($l['details'] ?? 'System Execution'); ?></p>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
    </div>
  </main>
  <script src="https://unpkg.com/lucide@latest"></script>
  <script>lucide.createIcons();</script>
</body>
</html>