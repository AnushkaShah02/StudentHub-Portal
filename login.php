<?php
// login.php (Practical 10)
require_once __DIR__ . '/config/db.php';

$error = $_GET['msg'] ?? '';

// Check remember-me cookie if session not set
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_user'])) {
    $token = $_COOKIE['remember_user'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE remember_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['last_activity'] = time();
    }
}

// Redirect if already authenticated
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_role'] === 'admin') {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: dashboard.html");
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember_me']);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Prevent Session Fixation
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['last_activity'] = time();

        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $upd = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
            $upd->execute([$token, $user['id']]);
            setcookie('remember_user', $token, time() + (86400 * 30), "/", "", false, true); // 30 days, HttpOnly
        }

        record_audit_log('USER_LOGIN', "Successful login for {$user['email']} as {$user['role']}");

        if ($user['role'] === 'admin') {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: dashboard.html");
        }
        exit;
    } else {
        $error = "Invalid college email credentials or password.";
        record_audit_log('FAILED_LOGIN_ATTEMPT', "Failed attempt for email: $email");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>StudentHub - CHARUSAT Login</title>
  <link rel="stylesheet" href="style.css">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,600;0,700&display=swap');
    .login-container { display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 15px; }
    .login-card { background: var(--bg-white); max-width: 420px; width: 100%; padding: 35px 30px; border-radius: var(--radius-organic-1); border: 1px solid rgba(109, 94, 247, 0.1); box-shadow: var(--shadow-hover); position: relative; z-index: 10; }
    .form-group { margin-bottom: 16px; position: relative; }
    .form-input { width: 100%; padding: 12px 15px 12px 45px; border: 1px solid var(--lavender); border-radius: var(--radius-capsule); outline: none; background: var(--bg-light); font-size: 0.9rem; }
    .form-icon { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted); }
    .error-tag { background: #ffebee; color: #c62828; padding: 10px 14px; border-radius: 12px; margin-bottom: 18px; font-size: 0.85rem; border: 1px solid #ffcdd2; }
  </style>
</head>
<body>
  <div class="bg-blob-container">
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
  </div>

  <main class="login-container">
    <section class="login-card">
      <div style="display:flex; justify-content:center; margin-bottom:15px;">
        <div style="background:var(--primary-light); color:var(--primary); padding:12px; border-radius:var(--radius-blob);">
          <i data-lucide="graduation-cap" style="width: 28px; height: 28px;"></i>
        </div>
      </div>
      <h1 class="font-poppins" style="text-align: center; font-size: 1.5rem; margin-bottom: 4px; color: var(--primary-dark);">StudentHub Portal</h1>
      <p style="text-align: center; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 22px;">Gateway Authentication (CHARUSAT)</p>

      <?php if (!empty($error)): ?>
        <div class="error-tag"><?= htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <form action="login.php" method="POST">
        <div class="form-group">
          <i data-lucide="mail" class="form-icon"></i>
          <input type="email" name="email" class="form-input" placeholder="Your College Email" required value="<?= htmlspecialchars($_POST['email'] ?? 'anushka.shah@student.edu') ?>">
        </div>

        <div class="form-group">
          <i data-lucide="lock" class="form-icon"></i>
          <input type="password" name="password" class="form-input" placeholder="Password" required value="Admin@1234">
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.85rem; margin-bottom: 22px;">
          <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; color: var(--text-muted);">
            <input type="checkbox" name="remember_me" checked style="accent-color: var(--primary);"> Remember me
          </label>
          <a href="#" style="color: var(--primary); font-weight: 500;">Forgot Password?</a>
        </div>

        <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; padding: 14px;">
          Enter Portal <i data-lucide="arrow-right" style="width: 16px;"></i>
        </button>
      </form>

      <p style="text-align: center; margin-top: 25px; font-size: 0.88rem; color: var(--text-muted);">
        New student? <a href="register.php" style="color: var(--primary); font-weight: 600;">Create an account</a>
      </p>
    </section>
  </main>
  <script src="https://unpkg.com/lucide@latest"></script>
  <script>lucide.createIcons();</script>
</body>
</html>