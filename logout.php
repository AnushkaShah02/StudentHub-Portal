<?php
// logout.php
require_once __DIR__ . '/config/db.php';

record_audit_log('USER_LOGOUT', "User logged out");

// Delete remember cookie
if (isset($_COOKIE['remember_user'])) {
    setcookie('remember_user', '', time() - 3600, '/');
}

session_unset();
session_destroy();
header("Location: login.php?msg=You have been successfully logged out.");
exit;