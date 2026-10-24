<?php
// api.php (Practical 13 & 15 Final Fix - Handles AJAX Events and Registrations)
header('Content-Type: application/json');
require_once __DIR__ . '/config/db.php';

$action = $_GET['action'] ?? 'list_events';

try {
    switch ($action) {
        case 'list_events':
            $type = $_GET['type'] ?? 'ALL';
            $query = "SELECT * FROM events";
            $params = [];

            if ($type !== 'ALL') {
                $query .= " WHERE type = ?";
                $params[] = $type;
            }
            $query .= " ORDER BY event_date ASC";

            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $data = $stmt->fetchAll();

            echo json_encode(['status' => 'success', 'count' => count($data), 'data' => $data]);
            break;

        case 'leaderboard':
            $stmt = $pdo->query("SELECT name, points, cgpa, department FROM students ORDER BY points DESC LIMIT 5");
            $data = $stmt->fetchAll();
            echo json_encode(['status' => 'success', 'data' => $data]);
            break;

        case 'register_event':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception("POST request method required.");
            }

            // Read JSON input or fallback to standard POST body
            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);
            $eventId = (int)($input['event_id'] ?? $_POST['event_id'] ?? 0);

            if ($eventId <= 0) {
                throw new Exception("Please select a valid event.");
            }

            // Check if event exists
            $check = $pdo->prepare("SELECT id, title FROM events WHERE id = ?");
            $check->execute([$eventId]);
            $event = $check->fetch();
            if (!$event) {
                throw new Exception("Event not found in the database.");
            }

            // Find current student ID or default to Student 1 (Anushka Shah)
            $studentId = 1;
            if (isset($_SESSION['user_id'])) {
                $sStmt = $pdo->prepare("SELECT id FROM students WHERE user_id = ?");
                $sStmt->execute([$_SESSION['user_id']]);
                $sData = $sStmt->fetch();
                if ($sData) {
                    $studentId = $sData['id'];
                }
            }

            $pdo->beginTransaction();

            // 1. Increment registration count
            $upd = $pdo->prepare("UPDATE events SET registrations = registrations + 1 WHERE id = ?");
            $upd->execute([$eventId]);

            // 2. Insert record into registrations table
            $regStmt = $pdo->prepare("INSERT INTO registrations (student_id, event_id) VALUES (?, ?)");
            $regStmt->execute([$studentId, $eventId]);

            $pdo->commit();

            record_audit_log('EVENT_AJAX_REGISTRATION', "Student ID #$studentId registered for Event: " . $event['title']);
            echo json_encode([
                'status' => 'success',
                'message' => 'Successfully enrolled in ' . htmlspecialchars($event['title']) . '!'
            ]);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Unknown API Endpoint.']);
            break;
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}