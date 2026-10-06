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
        $stmt = $pdo->prepare("SELECT * FROM participants WHERE participant_id = ?");
        $stmt->execute([$id]);
        $participant = $stmt->fetch();

        if (!$participant) {
            sendJsonResponse(['success' => false, 'error' => 'Participant not found'], 404);
        }

        // Fetch their registrations
        $regStmt = $pdo->prepare("
            SELECT 
                r.registration_id,
                r.registration_date,
                r.registration_status,
                r.payment_status,
                e.event_id,
                e.event_name,
                e.registration_fee,
                e.status as event_status,
                c.category_name,
                s.event_date,
                s.start_time,
                v.venue_name
            FROM registrations r
            JOIN events e ON r.event_id = e.event_id
            JOIN event_categories c ON e.category_id = c.category_id
            LEFT JOIN event_schedules s ON e.event_id = s.event_id
            LEFT JOIN venues v ON s.venue_id = v.venue_id
            WHERE r.participant_id = ?
            ORDER BY r.registration_id DESC
        ");
        $regStmt->execute([$id]);
        $participant['registrations'] = $regStmt->fetchAll();

        sendJsonResponse(['success' => true, 'data' => $participant]);
    } else {
        $sql = "
            SELECT 
                p.*,
                (SELECT COUNT(*) FROM registrations r WHERE r.participant_id = p.participant_id) as total_events_registered,
                (SELECT COUNT(*) FROM registrations r WHERE r.participant_id = p.participant_id AND r.registration_status = 'Confirmed') as confirmed_events
            FROM participants p
            WHERE 1=1
        ";
        $params = [];

        if (!empty($_GET['search'])) {
            $search = '%' . trim($_GET['search']) . '%';
            $sql .= " AND (p.participant_name LIKE ? OR p.email LIKE ? OR p.college LIKE ? OR p.phone LIKE ?)";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        $sql .= " ORDER BY p.participant_id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $participants = $stmt->fetchAll();

        sendJsonResponse(['success' => true, 'data' => $participants]);
    }
}

function handlePost($pdo) {
    $data = getJsonInput();

    if (empty($data['participant_name']) || empty($data['email']) || empty($data['college'])) {
        sendJsonResponse(['success' => false, 'error' => 'Name, email, and college are required.'], 400);
    }

    $name = trim($data['participant_name']);
    $email = trim($data['email']);
    $phone = trim($data['phone'] ?? '');
    $college = trim($data['college']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendJsonResponse(['success' => false, 'error' => 'Invalid email address format.'], 400);
    }

    try {
        // Unique email check
        $chk = $pdo->prepare("SELECT COUNT(*) FROM participants WHERE email = ?");
        $chk->execute([$email]);
        if ($chk->fetchColumn() > 0) {
            sendJsonResponse(['success' => false, 'error' => 'Participant with this email already exists.'], 400);
        }

        $stmt = $pdo->prepare("INSERT INTO participants (participant_name, email, phone, college) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $phone, $college]);
        $id = (int)$pdo->lastInsertId();

        sendJsonResponse(['success' => true, 'message' => 'Participant created successfully!', 'participant_id' => $id]);
    } catch (Exception $e) {
        sendJsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
    }
}

function handlePut($pdo) {
    $data = getJsonInput();

    if (empty($data['participant_id']) || empty($data['participant_name']) || empty($data['email'])) {
        sendJsonResponse(['success' => false, 'error' => 'ID, name, and email are required.'], 400);
    }

    $id = (int)$data['participant_id'];
    $name = trim($data['participant_name']);
    $email = trim($data['email']);
    $phone = trim($data['phone'] ?? '');
    $college = trim($data['college'] ?? 'Woxsen University');

    try {
        // Unique email check excluding self
        $chk = $pdo->prepare("SELECT COUNT(*) FROM participants WHERE email = ? AND participant_id != ?");
        $chk->execute([$email, $id]);
        if ($chk->fetchColumn() > 0) {
            sendJsonResponse(['success' => false, 'error' => 'Email is already used by another participant.'], 400);
        }

        $stmt = $pdo->prepare("UPDATE participants SET participant_name = ?, email = ?, phone = ?, college = ? WHERE participant_id = ?");
        $stmt->execute([$name, $email, $phone, $college, $id]);

        sendJsonResponse(['success' => true, 'message' => 'Participant updated successfully!']);
    } catch (Exception $e) {
        sendJsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
    }
}

function handleDelete($pdo) {
    $data = getJsonInput();
    $id = isset($_GET['id']) ? (int)$_GET['id'] : (int)($data['participant_id'] ?? 0);

    if (!$id) {
        sendJsonResponse(['success' => false, 'error' => 'Participant ID is required.'], 400);
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM participants WHERE participant_id = ?");
        $stmt->execute([$id]);

        if ($stmt->rowCount() > 0) {
            sendJsonResponse(['success' => true, 'message' => 'Participant and related registrations deleted.']);
        } else {
            sendJsonResponse(['success' => false, 'error' => 'Participant not found.'], 404);
        }
    } catch (Exception $e) {
        sendJsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
    }
}
