<?php
// admin_events.php (Practical 12: Event CRUD + Poster File Upload)
require_once __DIR__ . '/config/db.php';
check_session_timeout();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php?msg=Access denied: Administrative privileges required.");
    exit;
}

$msg = '';

// Handle Delete Operation
if (isset($_GET['delete_id'])) {
    $delId = (int)$_GET['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
    $stmt->execute([$delId]);
    record_audit_log('DELETE_EVENT', "Removed event ID: $delId");
    header("Location: admin_events.php?msg=Event record deleted.");
    exit;
}

// Handle Add / Edit Event
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $title = htmlspecialchars(trim($_POST['title']));
    $type = htmlspecialchars(trim($_POST['type']));
    $date = $_POST['event_date'];
    $country = htmlspecialchars(trim($_POST['country'] ?? 'India'));
    $state = htmlspecialchars(trim($_POST['state'] ?? 'Gujarat'));
    $city = htmlspecialchars(trim($_POST['city'] ?? 'Anand'));
    $venue = htmlspecialchars(trim($_POST['venue']));
    $description = htmlspecialchars(trim($_POST['description']));
    $fee = htmlspecialchars(trim($_POST['fee']));
    $seats = htmlspecialchars(trim($_POST['seats']));

    $posterName = 'default_event.png';

    // File Upload Validation (Size <= 2MB, Allowed Types: JPG, PNG, WEBP)
    if (isset($_FILES['poster']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $fileInfo = pathinfo($_FILES['poster']['name']);
        $ext = strtolower($fileInfo['extension'] ?? '');

        if (!in_array($ext, $allowed)) {
            $msg = "Error: Invalid poster image format. Allowed: PNG, JPG, JPEG, WEBP.";
        } elseif ($_FILES['poster']['size'] > (2 * 1024 * 1024)) {
            $msg = "Error: File size exceeds the 2MB boundary limit.";
        } else {
            $uploadDir = __DIR__ . '/uploads/posters/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $posterName = uniqid('poster_', true) . '.' . $ext;
            move_uploaded_file($_FILES['poster']['tmp_name'], $uploadDir . $posterName);
        }
    }

    if (empty($msg)) {
        if ($id) {
            $stmt = $pdo->prepare("UPDATE events SET title = ?, type = ?, event_date = ?, country = ?, state = ?, city = ?, venue = ?, description = ?, fee = ?, seats = ? WHERE id = ?");
            $stmt->execute([$title, $type, $date, $country, $state, $city, $venue, $description, $fee, $seats, $id]);
            record_audit_log('UPDATE_EVENT', "Updated event: $title");
            $msg = "Event updated successfully.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO events (title, type, event_date, country, state, city, venue, description, fee, seats, poster_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $type, $date, $country, $state, $city, $venue, $description, $fee, $seats, $posterName]);
            record_audit_log('CREATE_EVENT', "Created new event: $title");
            $msg = "New event published with validated poster banner.";
        }
    }
}

$events = $pdo->query("SELECT * FROM events ORDER BY event_date ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>StudentHub - Events Management</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .form-box { background: var(--bg-white); padding: 25px; border-radius: var(--radius-organic-1); margin-bottom: 30px; box-shadow: var(--shadow-soft); }
    .inputs-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 15px; }
    .inputs-grid input, .inputs-grid select, textarea { padding: 10px 14px; border: 1px solid var(--lavender); border-radius: var(--radius-capsule); outline: none; }
    textarea { border-radius: 12px; width: 100%; height: 90px; }
    @media (max-width: 992px) { .inputs-grid { grid-template-columns: 1fr; } }
  </style>
</head>
<body>
  <div class="bg-blob-container"><div class="blob blob-1"></div><div class="blob blob-2"></div></div>
  <header class="global-nav">
    <div class="nav-container">
      <div class="logo-group"><div class="logo-icon"><i data-lucide="calendar"></i></div><span class="logo-text">Events Console</span></div>
      <nav class="nav-links">
        <a href="admin_dashboard.php" class="nav-item"><i data-lucide="layout-dashboard" style="width:16px;"></i> Console</a>
        <a href="admin_students.php" class="nav-item"><i data-lucide="users" style="width:16px;"></i> Students CRUD</a>
        <a href="admin_events.php" class="nav-item active"><i data-lucide="calendar" style="width:16px;"></i> Events CRUD</a>
        <a href="logout.php" class="nav-item" style="color: #FF5A5A;"><i data-lucide="log-out" style="width:16px;"></i> Logout</a>
      </nav>
    </div>
  </header>

  <main class="main-wrapper">
    <h1 class="font-poppins" style="margin-bottom: 20px;">Manage Campus Events & Symposiums</h1>
    <?php if (!empty($msg) || !empty($_GET['msg'])): ?>
      <div style="background: #eaf7ea; color: #2e7d32; padding: 12px; border-radius: 12px; margin-bottom: 20px;">
        <?= htmlspecialchars($msg ?: $_GET['msg']); ?>
      </div>
    <?php endif; ?>

    <section class="form-box">
      <h3 class="font-poppins" style="font-size: 1.1rem; margin-bottom: 15px;">Host or Modify Campus Event</h3>
      <form action="admin_events.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" id="event-id">
        <div class="inputs-grid">
          <input type="text" name="title" id="event-title" placeholder="Event Title" required>
          <select name="type" id="event-type">
            <option value="HACKATHON">Hackathon</option>
            <option value="WORKSHOP">Workshop</option>
            <option value="SEMINAR">Seminar</option>
            <option value="SYMPOSIUM">Symposium</option>
          </select>
          <input type="date" name="event_date" id="event-date" required>
          <input type="text" name="venue" id="event-venue" placeholder="Venue / Room" required>
          <input type="text" name="fee" id="event-fee" placeholder="Fee (e.g. Free Entry)" required>
          <input type="text" name="seats" id="event-seats" placeholder="Seats (e.g. 150)" required>
        </div>
        <div style="margin-bottom: 15px;">
          <label style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted);">Event Poster (PNG/JPG under 2MB)</label>
          <input type="file" name="poster" accept="image/*" style="display: block; margin-top: 5px;">
        </div>
        <div style="margin-bottom: 15px;">
          <textarea name="description" id="event-desc" placeholder="Event Summary & syllabus coverage..." required></textarea>
        </div>
        <button type="submit" class="btn-primary">Publish Event <i data-lucide="send"></i></button>
      </form>
    </section>

    <!-- Cards Display of Active Events -->
    <div class="events-card-grid">
      <?php foreach ($events as $ev): ?>
        <article class="event-card-organic">
          <div class="event-badge-row">
            <span class="event-badge" style="background: var(--primary-light); color: var(--primary);"><?= $ev['type']; ?></span>
            <span style="font-size: 0.8rem; color: var(--text-muted);"><?= $ev['event_date']; ?></span>
          </div>
          <h4><?= htmlspecialchars($ev['title']); ?></h4>
          <p><?= htmlspecialchars($ev['description']); ?></p>
          <div class="event-meta-info">
            <span><i data-lucide="map-pin"></i> <?= htmlspecialchars($ev['venue']); ?></span>
            <span><i data-lucide="users"></i> <?= $ev['registrations']; ?> Enrolled / <?= htmlspecialchars($ev['seats']); ?> Max</span>
          </div>
          <div class="event-footer">
            <span class="event-fee"><?= htmlspecialchars($ev['fee']); ?></span>
            <div>
              <a href="admin_events.php?delete_id=<?= $ev['id']; ?>" onclick="return confirm('Delete event?');" style="color: #FF5A5A; font-size: 0.85rem; font-weight: 600;">Remove</a>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </main>
  <script src="https://unpkg.com/lucide@latest"></script>
  <script>lucide.createIcons();</script>
</body>
</html>