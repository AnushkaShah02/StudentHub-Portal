<?php
// admin_dashboard.php (Practical 14 Admin Command Center)
require_once __DIR__ . '/config/db.php';
check_session_timeout();

// Guard strictly for admin role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php?msg=Access denied: Administrative privileges required.");
    exit;
}

// Aggregation queries
$studentCount  = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$eventCount    = $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
$userCount     = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalRegs     = $pdo->query("SELECT COUNT(*) FROM registrations")->fetchColumn();
$avgAttendance = $pdo->query("SELECT ROUND(COALESCE(AVG(attendance), 0), 1) FROM students")->fetchColumn();

// Latest audit logs
$logs = $pdo->query("SELECT a.*, COALESCE(u.name, 'System') as user_name FROM audit_logs a LEFT JOIN users u ON a.user_id = u.id ORDER BY a.id DESC LIMIT 5")->fetchAll();

// Events summary
$eventsSummary = $pdo->query("SELECT id, title, type, event_date, registrations, seats FROM events ORDER BY id DESC LIMIT 4")->fetchAll();
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
    .admin-quick-actions { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 25px; }
    .action-card { background: var(--bg-white); padding: 18px; border-radius: var(--radius-organic-1); border: 1px solid rgba(109, 94, 247, 0.1); display: flex; align-items: center; gap: 15px; transition: var(--transition); }
    .action-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-hover); border-color: var(--primary); }
    .action-icon { width: 45px; height: 45px; border-radius: var(--radius-blob); display: flex; align-items: center; justify-content: center; }
    @media (max-width: 992px) { .admin-quick-actions { grid-template-columns: 1fr 1fr; } }
  </style>
</head>
<body>
  <div class="bg-blob-container"><div class="blob blob-1"></div><div class="blob blob-2"></div></div>

  <header class="global-nav">
    <div class="nav-container">
      <div class="logo-group">
        <div class="logo-icon" style="background: #2C2A38;"><i data-lucide="shield-check"></i></div>
        <span class="logo-text" style="color: #2C2A38;">CHARUSAT Admin Console</span>
      </div>
      <nav class="nav-links">
        <a href="admin_dashboard.php" class="nav-item active"><i data-lucide="layout-dashboard" style="width:16px;"></i> Admin Dashboard</a>
        <a href="admin_students.php" class="nav-item"><i data-lucide="users" style="width:16px;"></i> Student Registry</a>
        <a href="admin_events.php" class="nav-item"><i data-lucide="calendar" style="width:16px;"></i> Event Publisher</a>
        <a href="dashboard.html" class="nav-item"><i data-lucide="eye" style="width:16px;"></i> View Student Portal</a>
        <a href="logout.php" class="nav-item" style="color: #FF5A5A;"><i data-lucide="log-out" style="width:16px;"></i> Logout</a>
      </nav>
    </div>
  </header>

  <main class="main-wrapper">
    <div class="page-intro" style="margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center;">
      <div>
        <h1 class="font-poppins">Administrator Control Center</h1>
        <p style="color: var(--text-muted); margin-top: 4px;">Welcome, <strong><?= htmlspecialchars($_SESSION['user_name'] ?? 'Administrator'); ?></strong>. Connected to MySQL database.</p>
      </div>
      <span style="background: #EAF7EA; color: #4CAF50; padding: 6px 14px; border-radius: var(--radius-capsule); font-size: 0.8rem; font-weight: 700;">LIVE SYSTEM SECURE</span>
    </div>

    <!-- Quick action links -->
    <div class="admin-quick-actions">
      <a href="admin_students.php" class="action-card">
        <div class="action-icon" style="background: var(--primary-light); color: var(--primary);"><i data-lucide="user-plus"></i></div>
        <div>
          <h5 style="margin: 0; font-size: 0.95rem;">Add Student</h5>
          <span style="font-size: 0.75rem; color: var(--text-muted);">Manage Profiles</span>
        </div>
      </a>
      <a href="admin_events.php" class="action-card">
        <div class="action-icon" style="background: #FFF3E0; color: #FF9800;"><i data-lucide="calendar-plus"></i></div>
        <div>
          <h5 style="margin: 0; font-size: 0.95rem;">Host Event</h5>
          <span style="font-size: 0.75rem; color: var(--text-muted);">Upload Posters</span>
        </div>
      </a>
      <a href="events.html" class="action-card">
        <div class="action-icon" style="background: #EAF7EA; color: #4CAF50;"><i data-lucide="sparkles"></i></div>
        <div>
          <h5 style="margin: 0; font-size: 0.95rem;">Events Board</h5>
          <span style="font-size: 0.75rem; color: var(--text-muted);">Test Quick Register</span>
        </div>
      </a>
      <a href="data/contacts.csv" target="_blank" class="action-card">
        <div class="action-icon" style="background: #EDE7F6; color: #673AB7;"><i data-lucide="file-spreadsheet"></i></div>
        <div>
          <h5 style="margin: 0; font-size: 0.95rem;">CSV Inquiries</h5>
          <span style="font-size: 0.75rem; color: var(--text-muted);">View Stored Inquiries</span>
        </div>
      </a>
    </div>

    <!-- Aggregation Metrics -->
    <div class="grid-3" style="margin-bottom: 30px;">
      <article class="organic-card organic-card-1" style="padding: 24px;">
        <span style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--text-muted);">Enrolled Students</span>
        <div class="stat-badge" style="margin-top: 8px;"><?= $studentCount; ?></div>
        <p style="font-size: 0.8rem; color: var(--success); margin-top: 6px;"><i data-lucide="users" style="width: 14px;"></i> <?= $userCount; ?> System User Accounts</p>
      </article>

      <article class="organic-card organic-card-2" style="padding: 24px;">
        <span style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--text-muted);">Active Campus Events</span>
        <div class="stat-badge" style="margin-top: 8px; color: var(--warning);"><?= $eventCount; ?></div>
        <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 6px;"><i data-lucide="check-circle" style="width: 14px;"></i> <?= $totalRegs; ?> Total Student Registrations</p>
      </article>

      <article class="organic-card organic-card-1" style="padding: 24px;">
        <span style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--text-muted);">Overall CSE Attendance</span>
        <div class="stat-badge" style="margin-top: 8px; color: #4CAF50;"><?= $avgAttendance; ?>%</div>
        <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 6px;">Department Requirement: 75%</p>
      </article>
    </div>

    <div class="grid-2">
      <!-- Active Events Managed -->
      <section class="organic-card organic-card-1" style="padding: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
          <h3 class="font-poppins" style="font-size: 1.1rem;"><i data-lucide="calendar" style="color: var(--primary);"></i> Event Registrations Overview</h3>
          <a href="admin_events.php" style="font-size: 0.8rem; color: var(--primary); font-weight: 600;">Manage Events &rarr;</a>
        </div>
        <table class="table-container">
          <thead>
            <tr><th>Event</th><th>Type</th><th>Date</th><th>Enrolled</th></tr>
          </thead>
          <tbody>
            <?php foreach ($eventsSummary as $ev): ?>
            <tr>
              <td><strong><?= htmlspecialchars($ev['title']); ?></strong></td>
              <td><span style="font-size: 0.75rem; background: var(--primary-light); color: var(--primary); padding: 3px 8px; border-radius: 12px; font-weight: 600;"><?= $ev['type']; ?></span></td>
              <td><?= $ev['event_date']; ?></td>
              <td><strong><?= $ev['registrations']; ?></strong> / <?= htmlspecialchars($ev['seats']); ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </section>

      <!-- Practical 14 Audit Trail -->
      <section class="organic-card organic-card-2" style="padding: 24px;">
        <h3 class="font-poppins" style="font-size: 1.1rem; margin-bottom: 15px;"><i data-lucide="shield-alert" style="color: var(--primary);"></i> Security Audit Logs</h3>
        <div style="display: flex; flex-direction: column; gap: 10px;">
          <?php foreach ($logs as $l): ?>
            <div style="padding: 10px 14px; background: var(--bg-light); border-radius: var(--radius-organic-2); font-size: 0.82rem; border-left: 3px solid var(--primary);">
              <div style="display: flex; justify-content: space-between; font-weight: 600;">
                <span style="color: var(--primary-dark);"><?= htmlspecialchars($l['action']); ?></span>
                <span style="color: var(--text-muted); font-size: 0.72rem;"><?= $l['created_at']; ?></span>
              </div>
              <p style="color: var(--text-main); margin-top: 3px;"><?= htmlspecialchars($l['details'] ?? 'System Execution'); ?></p>
              <span style="font-size: 0.7rem; color: var(--text-muted);">Operator: <?= htmlspecialchars($l['user_name']); ?> | IP: <?= htmlspecialchars($l['ip_address'] ?? '127.0.0.1'); ?></span>
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