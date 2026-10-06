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
        $eventId = (int)$_GET['id'];
        
        // Single event details
        $stmt = $pdo->prepare("
            SELECT 
                e.*,
                c.category_name,
                c.description as category_description,
                o.organizer_name,
                o.email as organizer_email,
                o.phone as organizer_phone,
                o.department as organizer_department,
                s.schedule_id,
                s.event_date,
                s.start_time,
                s.end_time,
                v.venue_id,
                v.venue_name,
                v.location as venue_location,
                v.capacity as venue_capacity,
                v.venue_type,
                (SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.event_id AND r.registration_status = 'Confirmed') as confirmed_count,
                (SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.event_id) as total_registered
            FROM events e
            JOIN event_categories c ON e.category_id = c.category_id
            JOIN organizers o ON e.organizer_id = o.organizer_id
            LEFT JOIN event_schedules s ON e.event_id = s.event_id
            LEFT JOIN venues v ON s.venue_id = v.venue_id
            WHERE e.event_id = ?
        ");
        $stmt->execute([$eventId]);
        $event = $stmt->fetch();

        if (!$event) {
            sendJsonResponse(['success' => false, 'error' => 'Event not found'], 404);
        }

        // Also fetch list of participants registered for this event
        $partStmt = $pdo->prepare("
            SELECT 
                r.registration_id,
                r.registration_date,
                r.registration_status,
                r.payment_status,
                p.participant_id,
                p.participant_name,
                p.email,
                p.phone,
                p.college
            FROM registrations r
            JOIN participants p ON r.participant_id = p.participant_id
            WHERE r.event_id = ?
            ORDER BY r.registration_id DESC
        ");
        $partStmt->execute([$eventId]);
        $event['participants'] = $partStmt->fetchAll();

        sendJsonResponse(['success' => true, 'data' => $event]);
    } else {
        // All events with filter support
        $sql = "
            SELECT 
                e.event_id,
                e.event_name,
                e.description,
                e.registration_fee,
                e.max_participants,
                e.status,
                e.category_id,
                e.organizer_id,
                c.category_name,
                o.organizer_name,
                o.department as organizer_department,
                s.schedule_id,
                s.venue_id,
                s.event_date,
                s.start_time,
                s.end_time,
                v.venue_name,
                v.location as venue_location,
                v.capacity as venue_capacity,
                (SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.event_id AND r.registration_status = 'Confirmed') as confirmed_count,
                (SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.event_id) as total_registered,
                (e.max_participants - (SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.event_id AND r.registration_status = 'Confirmed')) as seats_left
            FROM events e
            JOIN event_categories c ON e.category_id = c.category_id
            JOIN organizers o ON e.organizer_id = o.organizer_id
            LEFT JOIN event_schedules s ON e.event_id = s.event_id
            LEFT JOIN venues v ON s.venue_id = v.venue_id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($_GET['category_id'])) {
            $sql .= " AND e.category_id = ?";
            $params[] = (int)$_GET['category_id'];
        }
        if (!empty($_GET['status'])) {
            $sql .= " AND e.status = ?";
            $params[] = $_GET['status'];
        }
        if (!empty($_GET['search'])) {
            $search = '%' . trim($_GET['search']) . '%';
            $sql .= " AND (e.event_name LIKE ? OR e.description LIKE ? OR o.organizer_name LIKE ?)";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        $sql .= " ORDER BY e.event_id DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $events = $stmt->fetchAll();

        sendJsonResponse(['success' => true, 'data' => $events]);
    }
}

function handlePost($pdo) {
    $data = getJsonInput();

    // Validation
    if (empty($data['event_name']) || empty($data['category_id']) || empty($data['organizer_id'])) {
        sendJsonResponse(['success' => false, 'error' => 'Event name, category, and organizer are required.'], 400);
    }

    $eventName = trim($data['event_name']);
    $description = $data['description'] ?? '';
    $categoryId = (int)$data['category_id'];
    $organizerId = (int)$data['organizer_id'];
    $fee = isset($data['registration_fee']) ? (float)$data['registration_fee'] : 0.00;
    $maxParticipants = isset($data['max_participants']) ? (int)$data['max_participants'] : 100;
    $status = !empty($data['status']) ? $data['status'] : 'Upcoming';

    if ($fee < 0) {
        sendJsonResponse(['success' => false, 'error' => 'Registration fee cannot be negative.'], 400);
    }
    if ($maxParticipants <= 0) {
        sendJsonResponse(['success' => false, 'error' => 'Max participants must be greater than 0.'], 400);
    }

    $validStatuses = ['Upcoming', 'Ongoing', 'Completed', 'Cancelled'];
    if (!in_array($status, $validStatuses)) {
        sendJsonResponse(['success' => false, 'error' => 'Invalid event status.'], 400);
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            INSERT INTO events (event_name, description, category_id, organizer_id, registration_fee, max_participants, status)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$eventName, $description, $categoryId, $organizerId, $fee, $maxParticipants, $status]);
        $eventId = (int)$pdo->lastInsertId();

        // Optional schedule creation
        if (!empty($data['venue_id']) && !empty($data['event_date']) && !empty($data['start_time']) && !empty($data['end_time'])) {
            $venueId = (int)$data['venue_id'];
            $eventDate = $data['event_date'];
            $startTime = $data['start_time'];
            $endTime = $data['end_time'];

            if ($endTime <= $startTime) {
                throw new Exception('End time must be after start time.');
            }

            // Check venue collision
            $collisionStmt = $pdo->prepare("
                SELECT COUNT(*) FROM event_schedules
                WHERE venue_id = ? AND event_date = ?
                AND NOT (end_time <= ? OR start_time >= ?)
            ");
            $collisionStmt->execute([$venueId, $eventDate, $startTime, $endTime]);
            if ($collisionStmt->fetchColumn() > 0) {
                throw new Exception('Selected venue is already booked for another event during this date and time.');
            }

            $schStmt = $pdo->prepare("
                INSERT INTO event_schedules (event_id, venue_id, event_date, start_time, end_time)
                VALUES (?, ?, ?, ?, ?)
            ");
            $schStmt->execute([$eventId, $venueId, $eventDate, $startTime, $endTime]);
        }

        $pdo->commit();
        sendJsonResponse(['success' => true, 'message' => 'Event created successfully!', 'event_id' => $eventId]);
    } catch (Exception $e) {
        $pdo->rollBack();
        sendJsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
    }
}

function handlePut($pdo) {
    $data = getJsonInput();

    if (empty($data['event_id'])) {
        sendJsonResponse(['success' => false, 'error' => 'Event ID is required.'], 400);
    }

    $eventId = (int)$data['event_id'];
    $eventName = trim($data['event_name'] ?? '');
    $description = $data['description'] ?? '';
    $categoryId = (int)($data['category_id'] ?? 0);
    $organizerId = (int)($data['organizer_id'] ?? 0);
    $fee = isset($data['registration_fee']) ? (float)$data['registration_fee'] : 0.00;
    $maxParticipants = isset($data['max_participants']) ? (int)$data['max_participants'] : 100;
    $status = $data['status'] ?? 'Upcoming';

    if (empty($eventName) || !$categoryId || !$organizerId) {
        sendJsonResponse(['success' => false, 'error' => 'Name, Category, and Organizer are required.'], 400);
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            UPDATE events 
            SET event_name = ?, description = ?, category_id = ?, organizer_id = ?, registration_fee = ?, max_participants = ?, status = ?
            WHERE event_id = ?
        ");
        $stmt->execute([$eventName, $description, $categoryId, $organizerId, $fee, $maxParticipants, $status, $eventId]);

        // If schedule information is provided, update or insert
        if (!empty($data['venue_id']) && !empty($data['event_date']) && !empty($data['start_time']) && !empty($data['end_time'])) {
            $venueId = (int)$data['venue_id'];
            $eventDate = $data['event_date'];
            $startTime = $data['start_time'];
            $endTime = $data['end_time'];

            if ($endTime <= $startTime) {
                throw new Exception('End time must be after start time.');
            }

            // Check collision excluding this event's schedule
            $collisionStmt = $pdo->prepare("
                SELECT COUNT(*) FROM event_schedules
                WHERE venue_id = ? AND event_date = ? AND event_id != ?
                AND NOT (end_time <= ? OR start_time >= ?)
            ");
            $collisionStmt->execute([$venueId, $eventDate, $eventId, $startTime, $endTime]);
            if ($collisionStmt->fetchColumn() > 0) {
                throw new Exception('Venue conflict: The selected venue is occupied during this time window.');
            }

            // Check if schedule already exists for this event
            $existingSch = $pdo->prepare("SELECT schedule_id FROM event_schedules WHERE event_id = ?");
            $existingSch->execute([$eventId]);
            $scheduleId = $existingSch->fetchColumn();

            if ($scheduleId) {
                $upSch = $pdo->prepare("
                    UPDATE event_schedules 
                    SET venue_id = ?, event_date = ?, start_time = ?, end_time = ?
                    WHERE schedule_id = ?
                ");
                $upSch->execute([$venueId, $eventDate, $startTime, $endTime, $scheduleId]);
            } else {
                $inSch = $pdo->prepare("
                    INSERT INTO event_schedules (event_id, venue_id, event_date, start_time, end_time)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $inSch->execute([$eventId, $venueId, $eventDate, $startTime, $endTime]);
            }
        }

        $pdo->commit();
        sendJsonResponse(['success' => true, 'message' => 'Event updated successfully!']);
    } catch (Exception $e) {
        $pdo->rollBack();
        sendJsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
    }
}

function handleDelete($pdo) {
    $data = getJsonInput();
    $id = isset($_GET['id']) ? (int)$_GET['id'] : (int)($data['event_id'] ?? 0);

    if (!$id) {
        sendJsonResponse(['success' => false, 'error' => 'Event ID is required.'], 400);
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM events WHERE event_id = ?");
        $stmt->execute([$id]);

        if ($stmt->rowCount() > 0) {
            sendJsonResponse(['success' => true, 'message' => 'Event and associated records deleted successfully.']);
        } else {
            sendJsonResponse(['success' => false, 'error' => 'Event not found or already deleted.'], 404);
        }
    } catch (Exception $e) {
        sendJsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
    }
}
