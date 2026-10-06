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
    if (isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $stmt = $pdo->prepare("SELECT * FROM venues WHERE venue_id = ?");
        $stmt->execute([$id]);
        $venue = $stmt->fetch();

        if (!$venue) {
            sendJsonResponse(['success' => false, 'error' => 'Venue not found'], 404);
        }

        // Fetch schedules booked for this venue
        $schStmt = $pdo->prepare("
            SELECT 
                s.schedule_id,
                s.event_date,
                s.start_time,
                s.end_time,
                e.event_id,
                e.event_name,
                e.status as event_status,
                c.category_name,
                o.organizer_name
            FROM event_schedules s
            JOIN events e ON s.event_id = e.event_id
            JOIN event_categories c ON e.category_id = c.category_id
            JOIN organizers o ON e.organizer_id = o.organizer_id
            WHERE s.venue_id = ?
            ORDER BY s.event_date ASC, s.start_time ASC
        ");
        $schStmt->execute([$id]);
        $venue['schedules'] = $schStmt->fetchAll();

        sendJsonResponse(['success' => true, 'data' => $venue]);
    } else {
        $sql = "
            SELECT 
                v.*,
                (SELECT COUNT(*) FROM event_schedules s WHERE s.venue_id = v.venue_id) as total_events_scheduled
            FROM venues v
            ORDER BY v.venue_name ASC
        ";
        $stmt = $pdo->query($sql);
        $venues = $stmt->fetchAll();

        sendJsonResponse(['success' => true, 'data' => $venues]);
    }
}

function handlePost($pdo) {
    $data = getJsonInput();

    if (empty($data['venue_name']) || empty($data['location']) || empty($data['capacity']) || empty($data['venue_type'])) {
        sendJsonResponse(['success' => false, 'error' => 'Name, location, capacity, and venue type are required.'], 400);
    }

    $name = trim($data['venue_name']);
    $location = trim($data['location']);
    $capacity = (int)$data['capacity'];
    $type = trim($data['venue_type']);

    if ($capacity <= 0) {
        sendJsonResponse(['success' => false, 'error' => 'Capacity must be greater than 0.'], 400);
    }

    try {
        $chk = $pdo->prepare("SELECT COUNT(*) FROM venues WHERE venue_name = ?");
        $chk->execute([$name]);
        if ($chk->fetchColumn() > 0) {
            sendJsonResponse(['success' => false, 'error' => 'A venue with this name already exists.'], 400);
        }

        $stmt = $pdo->prepare("INSERT INTO venues (venue_name, location, capacity, venue_type) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $location, $capacity, $type]);
        $id = (int)$pdo->lastInsertId();

        sendJsonResponse(['success' => true, 'message' => 'Venue added successfully!', 'venue_id' => $id]);
    } catch (Exception $e) {
        sendJsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
    }
}

function handlePut($pdo) {
    $data = getJsonInput();

    if (empty($data['venue_id']) || empty($data['venue_name']) || empty($data['capacity'])) {
        sendJsonResponse(['success' => false, 'error' => 'Venue ID, name, and capacity are required.'], 400);
    }

    $id = (int)$data['venue_id'];
    $name = trim($data['venue_name']);
    $location = trim($data['location'] ?? '');
    $capacity = (int)$data['capacity'];
    $type = trim($data['venue_type'] ?? 'Auditorium');

    if ($capacity <= 0) {
        sendJsonResponse(['success' => false, 'error' => 'Capacity must be greater than 0.'], 400);
    }

    try {
        $chk = $pdo->prepare("SELECT COUNT(*) FROM venues WHERE venue_name = ? AND venue_id != ?");
        $chk->execute([$name, $id]);
        if ($chk->fetchColumn() > 0) {
            sendJsonResponse(['success' => false, 'error' => 'A venue with this name already exists.'], 400);
        }

        $stmt = $pdo->prepare("UPDATE venues SET venue_name = ?, location = ?, capacity = ?, venue_type = ? WHERE venue_id = ?");
        $stmt->execute([$name, $location, $capacity, $type, $id]);

        sendJsonResponse(['success' => true, 'message' => 'Venue updated successfully!']);
    } catch (Exception $e) {
        sendJsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
    }
}

function handleDelete($pdo) {
    $data = getJsonInput();
    $id = isset($_GET['id']) ? (int)$_GET['id'] : (int)($data['venue_id'] ?? 0);

    if (!$id) {
        sendJsonResponse(['success' => false, 'error' => 'Venue ID is required.'], 400);
    }

    try {
        $chk = $pdo->prepare("SELECT COUNT(*) FROM event_schedules WHERE venue_id = ?");
        $chk->execute([$id]);
        if ($chk->fetchColumn() > 0) {
            sendJsonResponse(['success' => false, 'error' => 'Cannot delete venue: active event schedules are linked to this venue.'], 400);
        }

        $stmt = $pdo->prepare("DELETE FROM venues WHERE venue_id = ?");
        $stmt->execute([$id]);

        sendJsonResponse(['success' => true, 'message' => 'Venue deleted successfully.']);
    } catch (Exception $e) {
        sendJsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
    }
}
