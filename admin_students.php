<?php
// admin_students.php (Practical 11)
require_once __DIR__ . '/config/db.php';
check_session_timeout();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$msg = '';

// Handle Delete Operation
if (isset($_GET['delete_id'])) {
    $delId = (int)$_GET['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
    $stmt->execute([$delId]);
    record_audit_log('DELETE_STUDENT', "Deleted student ID: $delId");
    header("Location: admin_students.php?msg=Student successfully removed.");
    exit;
}

// Handle Add / Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $idNum = htmlspecialchars(trim($_POST['student_id_num']));
    $name = htmlspecialchars(trim($_POST['name']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $dept = htmlspecialchars(trim($_POST['department']));
    $cgpa = floatval($_POST['cgpa']);
    $attendance = floatval($_POST['attendance']);

    if ($id) {
        $stmt = $pdo->prepare("UPDATE students SET student_id_num = ?, name = ?, email = ?, department = ?, cgpa = ?, attendance = ? WHERE id = ?");
        $stmt->execute([$idNum, $name, $email, $dept, $cgpa, $attendance, $id]);
        record_audit_log('UPDATE_STUDENT', "Updated record for $name ($idNum)");
        $msg = "Student details updated successfully.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO students (student_id_num, name, email, department, cgpa, attendance, points) VALUES (?, ?, ?, ?, ?, ?, 100)");
        $stmt->execute([$idNum, $name, $email, $dept, $cgpa, $attendance]);
        record_audit_log('CREATE_STUDENT', "Created record for $name ($idNum)");
        $msg = "New student enrolled successfully.";
    }
}

// Search and Filter logic
$search = trim($_GET['search'] ?? '');
$query = "SELECT * FROM students";
$params = [];

if (!empty($search)) {
    $query .= " WHERE name LIKE ? OR student_id_num LIKE ? OR email LIKE ?";
    $params = ["%$search%", "%$search%", "%$search%"];
}
$query .= " ORDER BY id DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$students = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>StudentHub - Student Management CRUD</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .form-panel { background: var(--bg-white); padding: 25px; border-radius: var(--radius-organic-1); margin-bottom: 30px; box-shadow: var(--shadow-soft); }
    .inputs-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 15px; }
    .inputs-grid input, .inputs-grid select { padding: 10px 14px; border: 1px solid var(--lavender); border-radius: var(--radius-capsule); outline: none; }
    .table-container { width: 100%; border-collapse: collapse; margin-top: 15px; background: var(--bg-white); border-radius: var(--radius-organic-1); overflow: hidden; }
    .table-container th { padding: 14px; text-align: left; background: var(--bg-light); font-size: 0.8rem; color: var(--text-muted); }
    .table-container td { padding: 14px; font-size: 0.88rem; border-bottom: 1px dashed var(--lavender); }
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
        <div class="logo-icon"><i data-lucide="graduation-cap"></i></div>
        <span class="logo-text">StudentHub Admin</span>
      </div>
      <nav class="nav-links">
        <a href="admin_dashboard.php" class="nav-item"><i data-lucide="layout-dashboard" style="width:16px;"></i> Console</a>
        <a href="admin_students.php" class="nav-item active"><i data-lucide="users" style="width:16px;"></i> Students CRUD</a>
        <a href="admin_events.php" class="nav-item"><i data-lucide="calendar" style="width:16px;"></i> Events CRUD</a>
        <a href="logout.php" class="nav-item" style="color: #FF5A5A;"><i data-lucide="log-out" style="width:16px;"></i> Logout</a>
      </nav>
    </div>
  </header>

  <main class="main-wrapper">
    <div class="page-intro" style="margin-bottom: 25px;">
      <div>
        <h1 class="font-poppins">Student Academic Registry CRUD</h1>
        <p style="color: var(--text-muted);">Add, edit, search, and manage registered CSE student records.</p>
      </div>
    </div>

    <?php if (!empty($msg) || !empty($_GET['msg'])): ?>
      <div style="background: #eaf7ea; color: #2e7d32; padding: 12px 18px; border-radius: 12px; margin-bottom: 20px;">
        <?= htmlspecialchars($msg ?: $_GET['msg']); ?>
      </div>
    <?php endif; ?>

    <!-- Form Section -->
    <section class="form-panel">
      <h3 class="font-poppins" style="font-size: 1.15rem; margin-bottom: 15px;"><i data-lucide="user-plus" style="color: var(--primary);"></i> Add or Edit Student Profile</h3>
      <form action="admin_students.php" method="POST">
        <input type="hidden" name="id" id="edit-id">
        <div class="inputs-grid">
          <input type="text" name="student_id_num" id="edit-id-num" placeholder="Enrollment No (e.g. 25CS105)" required>
          <input type="text" name="name" id="edit-name" placeholder="Student Full Name" required>
          <input type="email" name="email" id="edit-email" placeholder="Campus Email Address" required>
          <select name="department" id="edit-dept">
            <option value="CSE">Computer Science & Engineering (CSE)</option>
            <option value="IT">Information Technology (IT)</option>
            <option value="ECE">Electronics & Communication (ECE)</option>
          </select>
          <input type="number" step="0.01" name="cgpa" id="edit-cgpa" placeholder="Current CGPA (0.0 - 10.0)" required>
          <input type="number" step="0.1" name="attendance" id="edit-att" placeholder="Attendance %" required>
        </div>
        <button type="submit" class="btn-primary">Save Student Details <i data-lucide="check"></i></button>
      </form>
    </section>

    <!-- Filter & Table Section -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
      <form method="GET" style="display: flex; gap: 10px; width: 100%; max-width: 400px;">
        <input type="text" name="search" placeholder="Search by name, enrollment, or email..." value="<?= htmlspecialchars($search); ?>" style="flex: 1; padding: 10px 14px; border-radius: var(--radius-capsule); border: 1px solid var(--lavender); outline: none;">
        <button type="submit" class="btn-primary" style="padding: 10px 18px;">Search</button>
      </form>
      <span style="font-size: 0.85rem; color: var(--text-muted);">Displaying <?= count($students); ?> students</span>
    </div>

    <table class="table-container">
      <thead>
        <tr>
          <th>Enrollment</th>
          <th>Full Name</th>
          <th>Email Address</th>
          <th>Dept</th>
          <th>CGPA</th>
          <th>Attendance</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($students as $row): ?>
        <tr>
          <td><strong><?= htmlspecialchars($row['student_id_num']); ?></strong></td>
          <td><?= htmlspecialchars($row['name']); ?></td>
          <td><?= htmlspecialchars($row['email']); ?></td>
          <td><?= htmlspecialchars($row['department']); ?></td>
          <td><span style="color: var(--primary); font-weight: 700;"><?= number_format($row['cgpa'], 2); ?></span></td>
          <td><?= $row['attendance']; ?>%</td>
          <td>
            <button onclick='editStudent(<?= json_encode($row); ?>)' style="background: none; border: none; color: var(--primary); cursor: pointer; font-weight: 600; margin-right: 8px;">Edit</button>
            <a href="admin_students.php?delete_id=<?= $row['id']; ?>" onclick="return confirm('Confirm deletion of this student profile?');" style="color: #FF5A5A; font-weight: 600;">Delete</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </main>

  <script src="https://unpkg.com/lucide@latest"></script>
  <script>
    lucide.createIcons();
    function editStudent(data) {
      document.getElementById('edit-id').value = data.id;
      document.getElementById('edit-id-num').value = data.student_id_num;
      document.getElementById('edit-name').value = data.name;
      document.getElementById('edit-email').value = data.email;
      document.getElementById('edit-dept').value = data.department;
      document.getElementById('edit-cgpa').value = data.cgpa;
      document.getElementById('edit-att').value = data.attendance;
      window.scrollTo({ top: 100, behavior: 'smooth' });
    }
  </script>
</body>
</html>