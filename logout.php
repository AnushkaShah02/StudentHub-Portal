<?php
// logout.php - Terminate session and remove cookies cleanly
require_once __DIR__ . '/config/db.php';

record_audit_log('USER_LOGOUT', "User logged out");

if (isset($_COOKIE['remember_user'])) {
    setcookie('remember_user', '', time() - 3600, '/');
}

session_unset();
session_destroy();
header("Location: login.php?msg=You have been successfully logged out.");
exit;