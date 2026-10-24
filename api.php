<?php
// api.php (Practical 13)
header('Content-Type: application/json');
require_once __DIR__ . '/config/db.php';

$action = $_GET['action'] ?? 'list_events';

try {
    switch ($action) {
        // 1. Asynchronous Event Fetching with Real-time Filters
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

        // 2. Fetch Leaderboard
        case 'leaderboard':
            $stmt = $pdo->query("SELECT name, points, cgpa, department FROM students ORDER BY points DESC LIMIT 5");
            $data = $stmt->fetchAll();
            echo json_encode(['status' => 'success', 'data' => $data]);
            break;

        // 3. AJAX Quick Register
        case 'register_event':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception("POST request method required.");
            }
            $input = json_decode(file_get_contents('php://input'), true);
            $eventId = (int)($input['event_id'] ?? 0);

            if ($eventId <= 0) throw new Exception("Invalid Event selected.");

            // Increment attendee counter
            $upd = $pdo->prepare("UPDATE events SET registrations = registrations + 1 WHERE id = ?");
            $upd->execute([$eventId]);

            record_audit_log('EVENT_AJAX_REGISTRATION', "User registered for Event ID #$eventId");
            echo json_encode(['status' => 'success', 'message' => 'Successfully enrolled in event!']);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Unknown API Endpoint.']);
            break;
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}