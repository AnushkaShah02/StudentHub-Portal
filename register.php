<?php
// register.php (Practical 5 & 9)
require_once __DIR__ . '/config/db.php';

$message = '';
$msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName   = htmlspecialchars(trim($_POST['first_name'] ?? ''));
    $lastName    = htmlspecialchars(trim($_POST['last_name'] ?? ''));
    $studentId   = htmlspecialchars(trim($_POST['student_id'] ?? ''));
    $dept        = htmlspecialchars(trim($_POST['dept'] ?? 'CSE 3rd Sem'));
    $email       = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $password    = $_POST['password'] ?? '';
    $confirmPass = $_POST['confirm_password'] ?? '';

    $fullName = trim($firstName . ' ' . $lastName);

    // Validation (Regex)
    if (strlen($fullName) < 3) {
        $message = "Please provide your full legal name.";
        $msgType = "danger";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid institutional email address format.";
        $msgType = "danger";
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters long.";
        $msgType = "danger";
    } elseif ($password !== $confirmPass) {
        $message = "Passwords do not match.";
        $msgType = "danger";
    } else {
        // Check Duplicate Email / Student ID
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);

        if ($check->rowCount() > 0) {
            $message = "An account with this email address already exists.";
            $msgType = "danger";
        } else {
            // Secure Password Hash
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $pdo->beginTransaction();
            try {
                // Insert into users
                $stmt1 = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'student')");
                $stmt1->execute([$fullName, $email, $hashedPassword]);
                $userId = $pdo->lastInsertId();

                // Insert into students profile table
                $stmt2 = $pdo->prepare("INSERT INTO students (user_id, student_id_num, name, email, department, semester, cgpa, attendance, points) VALUES (?, ?, ?, ?, 'CSE', '3rd Sem', 0.00, 100.0, 50)");
                $stmt2->execute([$userId, $studentId, $fullName, $email]);

                $pdo->commit();
                record_audit_log('STUDENT_REGISTRATION', "New student registered: $email ($studentId)");

                $message = "Account successfully registered! You can now log in.";
                $msgType = "success";
            } catch (Exception $e) {
                $pdo->rollBack();
                $message = "Registration failed: " . $e->getMessage();
                $msgType = "danger";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>StudentHub - Register</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .register-split { display: grid; grid-template-columns: 1fr 1.2fr; min-height: 100vh; }
    .banner-side { background: var(--primary); color: var(--bg-white); display: flex; flex-direction: column; justify-content: center; padding: 80px; }
    .form-side { display: flex; flex-direction: column; justify-content: center; padding: 60px 80px; background: var(--bg-white); }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px; }
    .input-wrapper { position: relative; }
    .input-field { width: 100%; padding: 14px 20px 14px 45px; border: 1px solid var(--lavender); border-radius: var(--radius-organic-1); outline: none; font-size: 0.9rem; }
    .input-icon { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-muted); width: 18px; }
    .alert-banner { padding: 12px 18px; border-radius: 12px; margin-bottom: 20px; font-size: 0.88rem; }
    .alert-success { background: #eaf7ea; color: #2e7d32; border: 1px solid #c8e6c9; }
    .alert-danger { background: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }
    @media (max-width: 992px) { .register-split { grid-template-columns: 1fr; } .banner-side { display: none; } .form-side { padding: 40px 20px; } .form-grid { grid-template-columns: 1fr; } }
  </style>
</head>
<body>
  <main class="register-split">
    <aside class="banner-side">
      <h2 class="font-poppins" style="font-size: 2.5rem; margin-bottom: 20px;">Begin Your<br>Academic Journey</h2>
      <p style="opacity: 0.85; margin-bottom: 40px;">Create your student account to tap directly into schedules, check dynamic GPA charts, and access course files inside a unified student landscape.</p>
      <div style="background: rgba(255, 255, 255, 0.15); padding: 30px; border-radius: var(--radius-organic-2);">
        <h4 style="margin-bottom: 10px;">Already Registered?</h4>
        <p style="font-size: 0.85rem; opacity: 0.9; margin-bottom: 20px;">If you already possess login details for your semester account, enter the portal below.</p>
        <a href="login.php" class="btn-primary" style="background: var(--bg-white); color: var(--primary);">Go to Login</a>
      </div>
    </aside>

    <section class="form-side">
      <div style="max-width: 600px; width:100%; margin: 0 auto;">
        <h2 class="font-poppins" style="font-size: 2rem; margin-bottom: 8px;">Create Account</h2>
        <p style="color: var(--text-muted); margin-bottom: 25px;">Complete the information below to register your semester profile.</p>

        <?php if (!empty($message)): ?>
          <div class="alert-banner alert-<?= $msgType; ?>"><?= htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form action="register.php" method="POST" id="reg-form">
          <div class="form-grid">
            <div class="input-wrapper">
              <i data-lucide="user" class="input-icon"></i>
              <input type="text" name="first_name" class="input-field" placeholder="First Name" required>
            </div>
            <div class="input-wrapper">
              <i data-lucide="user" class="input-icon"></i>
              <input type="text" name="last_name" class="input-field" placeholder="Last Name" required>
            </div>
            <div class="input-wrapper">
              <i data-lucide="hash" class="input-icon"></i>
              <input type="text" name="student_id" class="input-field" placeholder="Enrollment No (e.g. 25CS097)" required>
            </div>
            <div class="input-wrapper">
              <i data-lucide="layers" class="input-icon"></i>
              <input type="text" name="dept" class="input-field" placeholder="Dept / Semester" value="CSE 3rd Sem" required>
            </div>
            <div class="input-wrapper" style="grid-column: span 2;">
              <i data-lucide="mail" class="input-icon"></i>
              <input type="email" name="email" class="input-field" placeholder="College Email ID" required>
            </div>
            <div class="input-wrapper">
              <i data-lucide="lock" class="input-icon"></i>
              <input type="password" name="password" id="pass" class="input-field" placeholder="Password (min 6 chars)" required>
            </div>
            <div class="input-wrapper">
              <i data-lucide="lock" class="input-icon"></i>
              <input type="password" name="confirm_password" id="cpass" class="input-field" placeholder="Confirm Password" required>
            </div>
          </div>

          <label style="display: flex; align-items: flex-start; gap: 10px; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 25px; cursor: pointer;">
            <input type="checkbox" required style="accent-color: var(--primary); margin-top: 3px;">
            <span>I agree to the college portal terms of service and certify that my details are accurate.</span>
          </label>

          <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; padding: 14px;">
            Create Profile <i data-lucide="user-plus"></i>
          </button>
        </form>
      </div>
    </section>
  </main>
  <script src="https://unpkg.com/lucide@latest"></script>
  <script>lucide.createIcons();</script>
</body>
</html>