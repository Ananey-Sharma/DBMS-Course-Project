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
            r.registration_id,
            r.registration_date,
            r.registration_status,
            r.payment_status,
            e.event_id,
            e.event_name,
            e.registration_fee,
            e.status as event_status,
            e.max_participants,
            c.category_name,
            p.participant_id,
            p.participant_name,
            p.email as participant_email,
            p.phone as participant_phone,
            p.college
        FROM registrations r
        JOIN events e ON r.event_id = e.event_id
        JOIN event_categories c ON e.category_id = c.category_id
        JOIN participants p ON r.participant_id = p.participant_id
        WHERE 1=1
    ";
    $params = [];

    if (!empty($_GET['event_id'])) {
        $sql .= " AND r.event_id = ?";
        $params[] = (int)$_GET['event_id'];
    }
    if (!empty($_GET['participant_id'])) {
        $sql .= " AND r.participant_id = ?";
        $params[] = (int)$_GET['participant_id'];
    }
    if (!empty($_GET['registration_status'])) {
        $sql .= " AND r.registration_status = ?";
        $params[] = $_GET['registration_status'];
    }
    if (!empty($_GET['payment_status'])) {
        $sql .= " AND r.payment_status = ?";
        $params[] = $_GET['payment_status'];
    }
    if (!empty($_GET['search'])) {
        $search = '%' . trim($_GET['search']) . '%';
        $sql .= " AND (p.participant_name LIKE ? OR p.email LIKE ? OR e.event_name LIKE ? OR p.college LIKE ?)";
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
    }

    $sql .= " ORDER BY r.registration_id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $registrations = $stmt->fetchAll();

    sendJsonResponse(['success' => true, 'data' => $registrations]);
}

function handlePost($pdo) {
    $data = getJsonInput();

    if (empty($data['event_id'])) {
        sendJsonResponse(['success' => false, 'error' => 'Event ID is required.'], 400);
    }

    $eventId = (int)$data['event_id'];

    try {
        $pdo->beginTransaction();

        // 1. Fetch Event details & capacity
        $eventStmt = $pdo->prepare("SELECT event_name, registration_fee, max_participants, status FROM events WHERE event_id = ?");
        $eventStmt->execute([$eventId]);
        $event = $eventStmt->fetch();

        if (!$event) {
            throw new Exception("Selected event does not exist.");
        }
        if ($event['status'] === 'Cancelled') {
            throw new Exception("Cannot register for a cancelled event.");
        }
        if ($event['status'] === 'Completed') {
            throw new Exception("Cannot register for an event that has already completed.");
        }

        // 2. Capacity Check
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE event_id = ? AND registration_status = 'Confirmed'");
        $countStmt->execute([$eventId]);
        $confirmedCount = (int)$countStmt->fetchColumn();

        if ($confirmedCount >= (int)$event['max_participants']) {
            throw new Exception("Registration full! Maximum capacity of {$event['max_participants']} participants reached for this event.");
        }

        // 3. Resolve Participant (either by existing participant_id or create/find by email)
        $participantId = null;
        if (!empty($data['participant_id'])) {
            $participantId = (int)$data['participant_id'];
        } elseif (!empty($data['email']) && !empty($data['participant_name'])) {
            $email = trim($data['email']);
            $name = trim($data['participant_name']);
            $phone = trim($data['phone'] ?? '');
            $college = trim($data['college'] ?? 'Woxsen University');

            // Find existing participant by email
            $findPart = $pdo->prepare("SELECT participant_id FROM participants WHERE email = ?");
            $findPart->execute([$email]);
            $existingId = $findPart->fetchColumn();

            if ($existingId) {
                $participantId = (int)$existingId;
            } else {
                // Insert new participant
                $inPart = $pdo->prepare("INSERT INTO participants (participant_name, email, phone, college) VALUES (?, ?, ?, ?)");
                $inPart->execute([$name, $email, $phone, $college]);
                $participantId = (int)$pdo->lastInsertId();
            }
        } else {
            throw new Exception("Participant information is required.");
        }

        // 4. Duplicate Check (uq_event_participant)
        $dupStmt = $pdo->prepare("SELECT registration_id FROM registrations WHERE event_id = ? AND participant_id = ?");
        $dupStmt->execute([$eventId, $participantId]);
        if ($dupStmt->fetchColumn()) {
            throw new Exception("This participant is already registered for this event.");
        }

        // 5. Determine Registration & Payment Status
        $regStatus = !empty($data['registration_status']) ? $data['registration_status'] : 'Confirmed';
        $fee = (float)$event['registration_fee'];
        
        if ($fee == 0) {
            $paymentStatus = 'Not Required';
        } else {
            $paymentStatus = !empty($data['payment_status']) ? $data['payment_status'] : 'Pending';
        }

        $regDate = !empty($data['registration_date']) ? $data['registration_date'] : date('Y-m-d');

        $insStmt = $pdo->prepare("
            INSERT INTO registrations (event_id, participant_id, registration_date, registration_status, payment_status)
            VALUES (?, ?, ?, ?, ?)
        ");
        $insStmt->execute([$eventId, $participantId, $regDate, $regStatus, $paymentStatus]);
        $regId = (int)$pdo->lastInsertId();

        $pdo->commit();
        sendJsonResponse([
            'success' => true,
            'message' => 'Registration successfully created!',
            'registration_id' => $regId
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        sendJsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
    }
}

function handlePut($pdo) {
    $data = getJsonInput();

    if (empty($data['registration_id'])) {
        sendJsonResponse(['success' => false, 'error' => 'Registration ID is required.'], 400);
    }

    $regId = (int)$data['registration_id'];
    $regStatus = $data['registration_status'] ?? null;
    $payStatus = $data['payment_status'] ?? null;

    $validReg = ['Confirmed', 'Pending', 'Cancelled'];
    $validPay = ['Paid', 'Pending', 'Refunded', 'Not Required'];

    if ($regStatus && !in_array($regStatus, $validReg)) {
        sendJsonResponse(['success' => false, 'error' => 'Invalid registration status.'], 400);
    }
    if ($payStatus && !in_array($payStatus, $validPay)) {
        sendJsonResponse(['success' => false, 'error' => 'Invalid payment status.'], 400);
    }

    try {
        $updates = [];
        $params = [];

        if ($regStatus) {
            $updates[] = "registration_status = ?";
            $params[] = $regStatus;
        }
        if ($payStatus) {
            $updates[] = "payment_status = ?";
            $params[] = $payStatus;
        }

        if (empty($updates)) {
            sendJsonResponse(['success' => false, 'error' => 'No fields to update.'], 400);
        }

        $params[] = $regId;
        $sql = "UPDATE registrations SET " . implode(', ', $updates) . " WHERE registration_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        sendJsonResponse(['success' => true, 'message' => 'Registration updated successfully.']);
    } catch (Exception $e) {
        sendJsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
    }
}

function handleDelete($pdo) {
    $data = getJsonInput();
    $id = isset($_GET['id']) ? (int)$_GET['id'] : (int)($data['registration_id'] ?? 0);

    if (!$id) {
        sendJsonResponse(['success' => false, 'error' => 'Registration ID is required.'], 400);
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM registrations WHERE registration_id = ?");
        $stmt->execute([$id]);

        if ($stmt->rowCount() > 0) {
            sendJsonResponse(['success' => true, 'message' => 'Registration removed successfully.']);
        } else {
            sendJsonResponse(['success' => false, 'error' => 'Registration not found.'], 404);
        }
    } catch (Exception $e) {
        sendJsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
    }
}
