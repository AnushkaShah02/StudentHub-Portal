<?php
// process_contact.php (Practical 7)
require_once __DIR__ . '/config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: contact.html");
    exit;
}

$token = $_POST['csrf_token'] ?? '';
if (!verify_csrf_token($token)) {
    die("Error: Invalid CSRF security token.");
}

// Input Sanitization
$name       = htmlspecialchars(trim($_POST['full_name'] ?? ''));
$email      = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$department = htmlspecialchars(trim($_POST['department'] ?? 'General Support'));
$message    = htmlspecialchars(trim($_POST['message'] ?? ''));

$errors = [];
if (strlen($name) < 3) $errors[] = "Full Name must be at least 3 characters.";
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "A valid college email address is required.";
if (strlen($message) < 10) $errors[] = "Message should be at least 10 characters long.";

if (!empty($errors)) {
    echo "<script>alert('" . implode("\\n", $errors) . "'); window.history.back();</script>";
    exit;
}

// 1. Write to CSV Storage
$dir = __DIR__ . '/data';
if (!is_dir($dir)) mkdir($dir, 0777, true);

$csvFile = $dir . '/contacts.csv';
$csvData = [$name, $email, $department, $message, date('Y-m-d H:i:s')];
$fp = fopen($csvFile, 'a');
fputcsv($fp, $csvData);
fclose($fp);

// 2. Write to JSON Storage
$jsonFile = $dir . '/contacts.json';
$existing = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];
if (!is_array($existing)) $existing = [];

$existing[] = [
    'name' => $name,
    'email' => $email,
    'department' => $department,
    'message' => $message,
    'submitted_at' => date('Y-m-d H:i:s')
];
file_put_contents($jsonFile, json_encode($existing, JSON_PRETTY_PRINT));

record_audit_log('CONTACT_FORM_SUBMIT', "Query from $email ($department)");

echo "<script>
    alert('Thank you! Your inquiry was successfully recorded in both CSV and JSON datastores.');
    window.location.href = 'contact.html';
</script>";
exit;