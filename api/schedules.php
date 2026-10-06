<?php
require_once __DIR__ . '/../config/db.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        handleGet($pdo);
        break;
    case 'POST':
        handlePost($pdo);
        break;
    case 'PUT':
        handlePut($pdo);
        break;
    case 'DELETE':
        handleDelete($pdo);
        break;
    default:
        sendJsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
}

function handleGet($pdo) {
    $sql = "
        SELECT 
            s.schedule_id,
            s.event_date,
            s.start_time,
            s.end_time,
            e.event_id,
            e.event_name,
            e.status as event_status,
            e.registration_fee,
            c.category_name,
            v.venue_id,
            v.venue_name,
            v.location as venue_location,
            v.capacity as venue_capacity,
            v.venue_type,
            o.organizer_name
        FROM event_schedules s
        JOIN events e ON s.event_id = e.event_id
        JOIN event_categories c ON e.category_id = c.category_id
        JOIN venues v ON s.venue_id = v.venue_id
        JOIN organizers o ON e.organizer_id = o.organizer_id
        ORDER BY s.event_date ASC, s.start_time ASC
    ";
    $stmt = $pdo->query($sql);
    $schedules = $stmt->fetchAll();

    sendJsonResponse(['success' => true, 'data' => $schedules]);
}

function handlePost($pdo) {
    $data = getJsonInput();

    if (empty($data['event_id']) || empty($data['venue_id']) || empty($data['event_date']) || empty($data['start_time']) || empty($data['end_time'])) {
        sendJsonResponse(['success' => false, 'error' => 'Event, venue, date, start time, and end time are required.'], 400);
    }

    $eventId = (int)$data['event_id'];
    $venueId = (int)$data['venue_id'];
    $date = $data['event_date'];
    $startTime = $data['start_time'];
    $endTime = $data['end_time'];

    if ($endTime <= $startTime) {
        sendJsonResponse(['success' => false, 'error' => 'End time must be later than start time.'], 400);
    }

    try {
        // Venue overlap conflict check
        $colStmt = $pdo->prepare("
            SELECT s.schedule_id, e.event_name, s.start_time, s.end_time
            FROM event_schedules s
            JOIN events e ON s.event_id = e.event_id
            WHERE s.venue_id = ? AND s.event_date = ?
            AND NOT (s.end_time <= ? OR s.start_time >= ?)
        ");
        $colStmt->execute([$venueId, $date, $startTime, $endTime]);
        $conflict = $colStmt->fetch();

        if ($conflict) {
            sendJsonResponse([
                'success' => false,
                'error' => "Venue conflict! Already booked by '{$conflict['event_name']}' from {$conflict['start_time']} to {$conflict['end_time']}."
            ], 400);
        }

        $stmt = $pdo->prepare("
            INSERT INTO event_schedules (event_id, venue_id, event_date, start_time, end_time)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$eventId, $venueId, $date, $startTime, $endTime]);
        $id = (int)$pdo->lastInsertId();

        sendJsonResponse(['success' => true, 'message' => 'Schedule created successfully!', 'schedule_id' => $id]);
    } catch (Exception $e) {
        sendJsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
    }
}

function handlePut($pdo) {
    $data = getJsonInput();

    if (empty($data['schedule_id']) || empty($data['venue_id']) || empty($data['event_date']) || empty($data['start_time']) || empty($data['end_time'])) {
        sendJsonResponse(['success' => false, 'error' => 'All schedule fields are required.'], 400);
    }

    $scheduleId = (int)$data['schedule_id'];
    $venueId = (int)$data['venue_id'];
    $date = $data['event_date'];
    $startTime = $data['start_time'];
    $endTime = $data['end_time'];

    if ($endTime <= $startTime) {
        sendJsonResponse(['success' => false, 'error' => 'End time must be later than start time.'], 400);
    }

    try {
        // Collision check excluding self
        $colStmt = $pdo->prepare("
            SELECT s.schedule_id, e.event_name, s.start_time, s.end_time
            FROM event_schedules s
            JOIN events e ON s.event_id = e.event_id
            WHERE s.venue_id = ? AND s.event_date = ? AND s.schedule_id != ?
            AND NOT (s.end_time <= ? OR s.start_time >= ?)
        ");
        $colStmt->execute([$venueId, $date, $scheduleId, $startTime, $endTime]);
        $conflict = $colStmt->fetch();

        if ($conflict) {
            sendJsonResponse([
                'success' => false,
                'error' => "Venue conflict! Already booked by '{$conflict['event_name']}' from {$conflict['start_time']} to {$conflict['end_time']}."
            ], 400);
        }

        $stmt = $pdo->prepare("
            UPDATE event_schedules
            SET venue_id = ?, event_date = ?, start_time = ?, end_time = ?
            WHERE schedule_id = ?
        ");
        $stmt->execute([$venueId, $date, $startTime, $endTime, $scheduleId]);

        sendJsonResponse(['success' => true, 'message' => 'Schedule updated successfully!']);
    } catch (Exception $e) {
        sendJsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
    }
}

function handleDelete($pdo) {
    $data = getJsonInput();
    $id = isset($_GET['id']) ? (int)$_GET['id'] : (int)($data['schedule_id'] ?? 0);

    if (!$id) {
        sendJsonResponse(['success' => false, 'error' => 'Schedule ID is required.'], 400);
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM event_schedules WHERE schedule_id = ?");
        $stmt->execute([$id]);

        sendJsonResponse(['success' => true, 'message' => 'Schedule deleted successfully.']);
    } catch (Exception $e) {
        sendJsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
    }
}
