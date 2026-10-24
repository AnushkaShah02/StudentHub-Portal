<?php
// admin_events.php (Practical 12: Event CRUD + Poster File Upload)
require_once __DIR__ . '/config/db.php';
check_session_timeout();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php?msg=Access denied: Administrative privileges required.");
    exit;
}

$msg = '';
$msgType = 'success';

// Ensure directory exists
$uploadDir = __DIR__ . '/uploads/posters/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Handle Delete Operation
if (isset($_GET['delete_id'])) {
    $delId = (int)$_GET['delete_id'];
    
    // Optional: remove image file from disk
    $imgStmt = $pdo->prepare("SELECT poster_image FROM events WHERE id = ?");
    $imgStmt->execute([$delId]);
    $oldImg = $imgStmt->fetchColumn();
    if ($oldImg && $oldImg !== 'default_event.png' && file_exists($uploadDir . $oldImg)) {
        unlink($uploadDir . $oldImg);
    }

    $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
    $stmt->execute([$delId]);
    record_audit_log('DELETE_EVENT', "Removed event ID: $delId");
    header("Location: admin_events.php?msg=Event record deleted.&type=success");
    exit;
}

// Handle Add / Edit Event
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
    $title = htmlspecialchars(trim($_POST['title'] ?? ''));
    $type = htmlspecialchars(trim($_POST['type'] ?? 'WORKSHOP'));
    $date = $_POST['event_date'] ?? date('Y-m-d');
    $country = htmlspecialchars(trim($_POST['country'] ?? 'India'));
    $state = htmlspecialchars(trim($_POST['state'] ?? 'Gujarat'));
    $city = htmlspecialchars(trim($_POST['city'] ?? 'Anand'));
    $venue = htmlspecialchars(trim($_POST['venue'] ?? ''));
    $description = htmlspecialchars(trim($_POST['description'] ?? ''));
    $fee = htmlspecialchars(trim($_POST['fee'] ?? 'Free Entry'));
    $seats = htmlspecialchars(trim($_POST['seats'] ?? '100'));

    $posterName = 'default_event.png';

    // File Upload Validation (Size <= 2MB, Allowed Types: JPG, PNG, WEBP)
    if (isset($_FILES['poster']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $fileInfo = pathinfo($_FILES['poster']['name']);
        $ext = strtolower($fileInfo['extension'] ?? '');

        if (!in_array($ext, $allowed)) {
            $msg = "Error: Invalid poster image format. Allowed: PNG, JPG, JPEG, WEBP.";
            $msgType = "danger";
        } elseif ($_FILES['poster']['size'] > (2 * 1024 * 1024)) {
            $msg = "Error: File size exceeds the 2MB limit.";
            $msgType = "danger";
        } else {
            $posterName = uniqid('poster_', true) . '.' . $ext;
            move_uploaded_file($_FILES['poster']['tmp_name'], $uploadDir . $posterName);
        }
    }

    if (empty($msg)) {
        try {
            if ($id) {
                // Keep existing poster if no new one was uploaded
                if ($posterName !== 'default_event.png') {
                    $stmt = $pdo->prepare("UPDATE events SET title = ?, type = ?, event_date = ?, country = ?, state = ?, city = ?, venue = ?, description = ?, fee = ?, seats = ?, poster_image = ? WHERE id = ?");
                    $stmt->execute([$title, $type, $date, $country, $state, $city, $venue, $description, $fee, $seats, $posterName, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE events SET title = ?, type = ?, event_date = ?, country = ?, state = ?, city = ?, venue = ?, description = ?, fee = ?, seats = ? WHERE id = ?");
                    $stmt->execute([$title, $type, $date, $country, $state, $city, $venue, $description, $fee, $seats, $id]);
                }
                record_audit_log('UPDATE_EVENT', "Updated event: $title");
                $msg = "Event updated successfully.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO events (title, type, event_date, country, state, city, venue, description, fee, seats, poster_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $type, $date, $country, $state, $city, $venue, $description, $fee, $seats, $posterName]);
                record_audit_log('CREATE_EVENT', "Created new event: $title");
                $msg = "New event published with poster image.";
            }
        } catch (PDOException $e) {
            $msg = "Database Error: " . $e->getMessage();
            $msgType = "danger";
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
    .inputs-grid input, .inputs-grid select, textarea { padding: 10px 14px; border: 1px solid var(--lavender); border-radius: var(--radius-capsule); outline: none; width: 100%; }
    textarea { border-radius: 12px; width: 100%; height: 90px; }
    .alert-banner { padding: 12px 18px; border-radius: 12px; margin-bottom: 20px; font-size: 0.88rem; }
    .alert-success { background: #eaf7ea; color: #2e7d32; border: 1px solid #c8e6c9; }
    .alert-danger { background: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }
    
    /* Poster image display styling */
    .event-poster-wrapper {
      width: 100%;
      height: 160px;
      border-radius: 14px;
      overflow: hidden;
      margin-bottom: 14px;
      background: var(--bg-light);
      display: flex;
      align-items: center;
      justify-content: center;
      border: 1px solid rgba(109, 94, 247, 0.08);
    }
    .event-poster-img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    .poster-placeholder {
      color: var(--primary);
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 6px;
      font-size: 0.75rem;
      font-weight: 600;
    }
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
    
    <?php 
      $displayMsg = $msg ?: ($_GET['msg'] ?? '');
      $displayType = $msgType ?: ($_GET['type'] ?? 'success');
      if (!empty($displayMsg)): 
    ?>
      <div class="alert-banner alert-<?= $displayType === 'danger' ? 'danger' : 'success'; ?>">
        <?= htmlspecialchars($displayMsg); ?>
      </div>
    <?php endif; ?>

    <section class="form-box">
      <h3 class="font-poppins" style="font-size: 1.1rem; margin-bottom: 15px;">Host or Modify Campus Event</h3>
      <form action="admin_events.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" id="event-id">
        <div class="inputs-grid">
          <input type="text" name="title" id="event-title" placeholder="Event Title (e.g. AI Hackathon)" required>
          <select name="type" id="event-type">
            <option value="HACKATHON">Hackathon</option>
            <option value="WORKSHOP">Workshop</option>
            <option value="SEMINAR">Seminar</option>
            <option value="SYMPOSIUM">Symposium</option>
          </select>
          <input type="date" name="event_date" id="event-date" required value="<?= date('Y-m-d'); ?>">
          <input type="text" name="country" id="event-country" placeholder="Country" value="India" required>
          <input type="text" name="state" id="event-state" placeholder="State" value="Gujarat" required>
          <input type="text" name="city" id="event-city" placeholder="City" value="Anand" required>
          <input type="text" name="venue" id="event-venue" placeholder="Venue / Room" required>
          <input type="text" name="fee" id="event-fee" placeholder="Fee (e.g. Free Entry, $10)" required>
          <input type="text" name="seats" id="event-seats" placeholder="Seats (e.g. 150)" required>
        </div>
        <div style="margin-bottom: 15px;">
          <label style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted);">Event Poster (PNG/JPG/WEBP under 2MB)</label>
          <input type="file" name="poster" accept="image/*" style="display: block; margin-top: 5px;">
        </div>
        <div style="margin-bottom: 15px;">
          <textarea name="description" id="event-desc" placeholder="Event Summary & syllabus coverage..." required></textarea>
        </div>
        <button type="submit" class="btn-primary">Publish Event <i data-lucide="send"></i></button>
      </form>
    </section>

    <!-- Cards Display of Active Events with Posters -->
    <div class="events-card-grid">
      <?php foreach ($events as $ev): ?>
        <article class="event-card-organic">
          
          <!-- Poster Image Rendering Block -->
          <div class="event-poster-wrapper">
            <?php 
              $posterPath = 'uploads/posters/' . ($ev['poster_image'] ?? '');
              if (!empty($ev['poster_image']) && $ev['poster_image'] !== 'default_event.png' && file_exists(__DIR__ . '/' . $posterPath)): 
            ?>
              <img src="<?= htmlspecialchars($posterPath); ?>" alt="<?= htmlspecialchars($ev['title']); ?>" class="event-poster-img">
            <?php else: ?>
              <div class="poster-placeholder">
                <i data-lucide="image" style="width: 28px; height: 28px;"></i>
                <span>CHARUSAT Event Poster</span>
              </div>
            <?php endif; ?>
          </div>

          <div class="event-badge-row">
            <span class="event-badge" style="background: var(--primary-light); color: var(--primary);"><?= $ev['type']; ?></span>
            <span style="font-size: 0.8rem; color: var(--text-muted);"><?= $ev['event_date']; ?></span>
          </div>
          <h4><?= htmlspecialchars($ev['title']); ?></h4>
          <p><?= htmlspecialchars($ev['description']); ?></p>
          <div class="event-meta-info">
            <span><i data-lucide="map-pin"></i> <?= htmlspecialchars($ev['venue']); ?> (<?= htmlspecialchars($ev['city']); ?>)</span>
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