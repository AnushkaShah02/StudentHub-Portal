<?php
// process_contact.php (Practical 7: Server-side Form Processor & Dual File Storage)
require_once __DIR__ . '/config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: contact.html");
    exit;
}

// 1. Input Sanitization
$name       = htmlspecialchars(trim($_POST['full_name'] ?? ''));
$email      = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$department = htmlspecialchars(trim($_POST['department'] ?? 'General Support'));
$message    = htmlspecialchars(trim($_POST['message'] ?? ''));

$errors = [];
if (strlen($name) < 3) {
    $errors[] = "Full Name must be at least 3 characters.";
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "A valid college email address is required.";
}
if (strlen($message) < 5) {
    $errors[] = "Message should be at least 5 characters long.";
}

if (!empty($errors)) {
    echo "<script>alert('" . implode("\\n", $errors) . "'); window.history.back();</script>";
    exit;
}

// 2. Ensure data folder exists
$dir = __DIR__ . '/data';
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

$timestamp = date('Y-m-d H:i:s');

// 3. Append to CSV File
$csvFile = $dir . '/contacts.csv';
$csvExists = file_exists($csvFile);
$fp = fopen($csvFile, 'a');

// Add header if creating file for the first time
if (!$csvExists) {
    fputcsv($fp, ['Name', 'Email', 'Department', 'Message', 'Submitted_At']);
}

fputcsv($fp, [$name, $email, $department, $message, $timestamp]);
fclose($fp);

// 4. Append to JSON File
$jsonFile = $dir . '/contacts.json';
$existing = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];
if (!is_array($existing)) {
    $existing = [];
}

$existing[] = [
    'name'         => $name,
    'email'        => $email,
    'department'   => $department,
    'message'      => $message,
    'submitted_at' => $timestamp
];

file_put_contents($jsonFile, json_encode($existing, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

// 5. Record Audit Log (Practical 14 Integration)
record_audit_log('CONTACT_FORM_SUBMISSION', "Inquiry from $name ($email)");

// 6. Redirect back with confirmation
echo "<script>
    alert('Thank you! Your message has been safely saved to both contacts.csv and contacts.json.');
    window.location.href = 'contact.html';
</script>";
exit;